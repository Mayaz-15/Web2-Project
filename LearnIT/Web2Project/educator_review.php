<?php
// educator_review.php
// Receives: POST id, decision ('approve'|'disapprove'), comments (optional)
// Does: update recommendedquestion + insert into quizquestion if approved
// Returns: JSON  { ok: true }  on success, else  { ok:false, error:"..." }

session_start();
require_once __DIR__ . '/connect.php';

header('Content-Type: application/json');

// ---- Auth: must be logged-in educator ----
if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['user_type']) ||
    strtolower($_SESSION['user_type']) !== 'educator'
) {
    echo json_encode(['ok' => false, 'error' => 'not_authorized']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

// ---- DB driver type helpers ----
$isPDO    = ($conn instanceof PDO);
$isMySQLi = ($conn instanceof mysqli);

// ---- Read & validate input from AJAX ----
$id       = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$decision = isset($_POST['decision']) ? strtolower(trim($_POST['decision'])) : '';
$comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';

if ($id <= 0 || !in_array($decision, ['approve', 'disapprove'], true)) {
    echo json_encode(['ok' => false, 'error' => 'bad_input']);
    exit;
}

$newStatus = ($decision === 'approve') ? 'approved' : 'disapproved';

try {

    // ---- 1) Check that this recommendation belongs to this educator & is pending ----
    if ($isPDO) {
        $chk = $conn->prepare("
            SELECT rq.*
            FROM recommendedquestion rq
            JOIN Quiz q ON q.id = rq.quizID
            WHERE rq.id = ? AND q.educatorID = ? AND LOWER(rq.status) = 'pending'
            LIMIT 1
        ");
        $chk->execute([$id, $userId]);
        $rec = $chk->fetch(PDO::FETCH_ASSOC) ?: null;

    } elseif ($isMySQLi) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $chk = $conn->prepare("
            SELECT rq.*
            FROM recommendedquestion rq
            JOIN Quiz q ON q.id = rq.quizID
            WHERE rq.id = ? AND q.educatorID = ? AND LOWER(rq.status) = 'pending'
            LIMIT 1
        ");
        $chk->bind_param('ii', $id, $userId);
        $chk->execute();
        $rec = $chk->get_result()->fetch_assoc();
    } else {
        throw new Exception('no_db_connection');
    }

    if (!$rec) {
        echo json_encode(['ok' => false, 'error' => 'not_allowed']);
        exit;
    }

    // ---- 2) Begin transaction ----
    if ($isPDO) {
        $conn->beginTransaction();
    } else {
        $conn->begin_transaction();
    }

    // ---- 3) Update recommendedquestion: status + comments ----
    if ($isPDO) {
        $u = $conn->prepare("
            UPDATE recommendedquestion
            SET status = ?, comments = ?
            WHERE id = ?
        ");
        $u->execute([$newStatus, $comments, $id]);
        if ($u->rowCount() < 1) {
            throw new Exception('update_failed');
        }

    } else {
        $u = $conn->prepare("
            UPDATE recommendedquestion
            SET status = ?, comments = ?
            WHERE id = ?
        ");
        $u->bind_param('ssi', $newStatus, $comments, $id);
        $u->execute();
        if ($u->affected_rows < 1) {
            throw new Exception('update_failed');
        }
    }

    // ---- 4) If approved, copy it into quizquestion ----
    if ($decision === 'approve') {
        if ($isPDO) {
            $ins = $conn->prepare("
                INSERT INTO quizquestion
                    (quizID, question, questionFigureFileName,
                     answerA, answerB, answerC, answerD, correctAnswer)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([
                (int) $rec['quizID'],
                $rec['question'],
                $rec['questionFigureFileName'],
                $rec['answerA'],
                $rec['answerB'],
                $rec['answerC'],
                $rec['answerD'],
                $rec['correctAnswer']
            ]);
            if ($ins->rowCount() < 1) {
                throw new Exception('insert_failed');
            }

        } else {
            $ins = $conn->prepare("
                INSERT INTO quizquestion
                    (quizID, question, questionFigureFileName,
                     answerA, answerB, answerC, answerD, correctAnswer)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $quizID      = (int) $rec['quizID'];
            $qText       = $rec['question'];
            $qFigure     = $rec['questionFigureFileName'];
            $aA          = $rec['answerA'];
            $aB          = $rec['answerB'];
            $aC          = $rec['answerC'];
            $aD          = $rec['answerD'];
            $correctAns  = $rec['correctAnswer'];

            $ins->bind_param(
                'isssssss',
                $quizID, $qText, $qFigure,
                $aA, $aB, $aC, $aD, $correctAns
            );
            $ins->execute();
            if ($ins->affected_rows < 1) {
                throw new Exception('insert_failed');
            }
        }
    }

    // ---- 5) Commit & return success JSON ----
    if ($isPDO) {
        $conn->commit();
    } else {
        $conn->commit();
    }

    echo json_encode(['ok' => true]);
    exit;

} catch (Throwable $e) {

    if ($isPDO && $conn->inTransaction()) {
        $conn->rollBack();
    }
    if ($isMySQLi) {
        $conn->rollback();
    }

    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
}
