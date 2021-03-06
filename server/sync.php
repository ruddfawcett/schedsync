<?php
  require('./connect.php');

  header_remove();
  header('Access-Control-Allow-Origin: *');
  header('Content-type:application/json;charset=utf-8');

  if (!isset($_GET['v'])) {
    exit();
  }

  if (!isset($_POST['data'])) {
    http_response_code(422);
    echo json_encode(array('status' => 'error'));
    exit();
  }

  $student = $_POST['data']['student'];
  $courses = $_POST['data']['courses'];

  $stmt = $db->prepare("DELETE FROM paschedules WHERE student_id=?");
  $stmt->execute(array($student['id']));

  $query = "INSERT INTO paschedules (course_id, course_code, course_room, teacher_name, student_id, student_name, student_email, student_grad) VALUES (:course_id, :course_code, :course_room, :teacher_name, :student_id, :student_name, :student_email, :student_grad) ON CONFLICT (course_id, student_id) DO NOTHING";
  $stmt = $db->prepare($query);

  foreach ($courses as $course) {
    $stmt->execute([
      ':course_id' => $course['course_id'],
      ':course_code' => $course['course_code'],
      ':course_room' => $course['course_room'],
      ':teacher_name' => $course['teacher_name'],
      ':student_id' => $student['id'],
      ':student_name' => $student['name'],
      ':student_email' => $student['email'],
      ':student_grad' => $student['grad']
    ]);
  }

  $db->beginTransaction();

  try {
    $db->commit();
    http_response_code(200);
    echo json_encode(array('status' => 'success'));
  } catch (PDOException $e) {
    $db->rollBack();
    http_response_code(422);
    echo json_encode(array('status' => 'error'));
  }
?>
