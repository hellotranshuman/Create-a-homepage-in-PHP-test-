<?php
/**
 * Created by PhpStorm.
 * User: hello
 * Date: 2017-09-13
 * Time: 오전 11:18
 *
 * DBMS에 접근하여 값을 저장하거나 저장 된 값을 갱신하거나 하는 등등 -> DB 작업 전반을 수행
 * 새글, 수정, 삭제 -> 모두 수행
 *
 * 받을 값
 * ● 로그인을 표시하는 세션값
 * --> 로그인 정보가 정확한지 확인
 *
 * ● 수정 작업
 * --> 글 번호(해당 글 번호의 값을 수정 된 값으로 변경하기 위해 필요), 해당 글의 등록날짜는 수정 날짜로 변경하기
 *
 * ● 삭제 작업을 수행하는지 여부
 * --> 삭제 작업과 다른 작업을 구분하기 위해 필요
 *
 * ● 글 제목, 글 내용 값
 * --> DB에 저장하기 (글 제목, 글 내용을 포함한 등록날짜, 조회수 등등의 모든 정보를 같이 저장)
 *
 * ----------> 새글 또는 삭제작업 일 경우 여기까지 후 리스트 페이지의 첫화면으로 가면된다(디폴트 설정)
 */

// 글 번호, 새글 -> 0, 수정 -> 각각의 글 번호
$boardID = $_POST['boardID'];

// 로그인 여부를 확인하는 세션 값, 1 -> 로그인, 0 -> 로그인 아님
$loginValue = $_SESSION['loginState'];

// 해당 작업을 수행하는 사용자의 아이디를 받습니다.
if(isset($_SESSION['userID'])){
    $userID = $_SESSION['userID'];
} else
    $userID = "tempID";

// 해당 작업을 수행하는 사용자의 회원 등급을 받습니다.
if(isset($_SESSION['userGrade'])){
    $userGrade = $_SESSION['userGrade'];
} else
    $userGrade = "tempG";

// 글 제목, 삭제 작업의 경우 전달 받을 필요가 없으므로 전달 받지 못할 때는 0으로 초기화합니다.
if(isset($_POST['titleText'])){
    $titleText = $_POST['titleText'];
} else
    $titleText = 0;

// 글 내용, 삭제 작업의 경우 전달 받을 필요가 없으므로 전달 받지 못할 때는 0으로 초기화합니다.
if(isset($_POST['contentsText'])){
    $contentsText = $_POST['contentsText'];
} else
    $contentsText = 0;

// 삭제 작업인지 여부
// 삭제 작업 -> 1, 삭제 작업이 아니면 -> 0
if(isset($_POST['deleteCheck'])){
    $deleteCheck = $_POST['deleteCheck'];
    // 자료형을 int로 변경합니다.
    settype($deleteCheck, "integer");
} else
    $deleteCheck = 0;

// 작성된 댓글의 값을 받습니다.
if(isset($_POST['commentText'])){
    $commentText = $_POST['commentText'];
}
// 전달 받은 댓글의 값이 없는 경우, 즉 댓글 작성이 아닌 경우 0으로 초기화 합니다.
else
    $commentText = NULL;

// DB에 저장되어 있는 댓글의 글번호를 받습니다.
if(isset($_POST['commentBoardID'])){
    $commentBoardID = $_POST['commentBoardID'];
}
else
    $commentBoardID = NULL;

// 댓글의 update 인지 여부를 체크하는 값
if(isset($_POST['commentUpdateCheck'])){
    $commentUpdateCheck = $_POST['commentUpdateCheck'];

    // 자료형을 int로 변경합니다.
    settype($commentUpdateCheck, "integer");
}
else
    $commentUpdateCheck = 0;

// 댓글 update 또는 delete일 경우 원래 페이지로 이동하는데 이용되는 값
$commentValuePageMove = NULL;

//MySQL 서버 주소
define("HOST","localhost");
//DB 사용자 계정
define("USER","newBoardUser");
//DB 비밀번호 -> eP9DW8a92e2wxQhE
define("DBPASSWD","eP9DW8a92e2wxQhE");
//DB 이름
define("DBNAME","new_bulletin_board");
//글 정보가 들어 있는 테이블 명
$tableName = "boarddata";

// DB에 연결합니다.
$connect = mysqli_connect(HOST, USER, DBPASSWD, DBNAME);

// DB연결에 성공하였을 경우
if($connect){
    // 글번호를 나타내는 $boardID의 값이 0일 경우 새글이라는 의미이므로 insert문을 실행합니다.
    if($boardID == 0){
        // DB에 새글을 저장하는 insert문
        $insertORUpdateORDeleteSQL =
            "INSERT INTO $tableName(user_id, user_grade, title, contents, hits, reg_date)
               VALUES('$userID','$userGrade' , '$titleText', '$contentsText', 0, now())";
    }
    // 글번호를 나타내는 $boardID의 값이 0이 아니라는 것은 수정이라는 의미이므로 UPDATE를 실행합니다.
    else if($boardID != 0 && $deleteCheck == 0 && $commentText == NULL){
        $insertORUpdateORDeleteSQL =
            "UPDATE $tableName
            SET title = '$titleText', contents = '$contentsText', reg_date = now()
            WHERE board_id = $boardID";
    }
    // 삭제 작업, 삭제 작업 -> 1, 삭제 작업이 아니면 -> 0
    // $commentText의 값이 0이라는 것은 댓글은 아니라는 것이다.
    else if($deleteCheck != 0 && $commentText == NULL){
        // 지정한 글번호의 글을 삭제하는 SQL문
        $insertORUpdateORDeleteSQL =
            "DELETE FROM $tableName WHERE board_id = $boardID OR board_pid = $boardID";
    }
    // 댓글 값인 경우 INSERT
    else if($commentText != NULL && $boardID != 0 && $deleteCheck == 0 && $commentUpdateCheck == 0){
        // DB에 새 댓글을 저장하는 insert문
        $insertORUpdateORDeleteSQL =
            "INSERT INTO $tableName(board_pid, user_id, user_grade, contents, hits, reg_date)
               VALUES('$boardID', '$userID', '$userGrade', '$commentText', 0, now())";
    }
    // 댓글 값인 경우 UPDATE
    else if($commentText != NULL && $boardID != 0 && $deleteCheck == 0){
        $insertORUpdateORDeleteSQL =
            "UPDATE $tableName
            SET contents = '$commentText', reg_date = now()
            WHERE board_pid = $boardID AND board_id = $commentBoardID";

        $commentValuePageMove = 1;
    }
    // 댓글 값인 경우 DELETE
    else if($commentText != NULL && $deleteCheck != 0){
        $insertORUpdateORDeleteSQL =
            "DELETE FROM $tableName 
             WHERE board_pid = $boardID AND board_id = $commentBoardID";

        $commentValuePageMove = 1;
    }

    // 추가, 수정, 삭제 중 지정된 SQL문을 실행합니다.
    $startQuery = mysqli_query($connect, $insertORUpdateORDeleteSQL);

    // 댓글 관련 insert 작업인 경우
    if($commentText != NULL && $deleteCheck == 0){
        // DB에서 해당 글의 board_id와 같은 board_pid를 가진 값의
        // 유저 아이디, 회원 등급, 내용, 날짜를 SELECT하는 SQL문
        $commentSelectSQL =
            "SELECT board_id, user_id, user_grade, contents, reg_date
             FROM $tableName 
             WHERE board_pid = $boardID AND board_id = $commentBoardID";

        // 이 글의 댓글에 해당하는 글의 정보를 SELECT하는 SQL문을 실행합니다.
        $commentSelectQuery = mysqli_query($connect, $commentSelectSQL);

        $commentInfo = mysqli_fetch_array($commentSelectQuery);

        // 문자열에 존재하는 태그를 제거합니다.
        $commentInfo[3] = strip_tags($commentInfo[3]);

        echo "$commentInfo[0]_$commentInfo[1]_$commentInfo[2]_$commentInfo[3]_$commentInfo[4]";
    }

    // DB 접속을 종료합니다.
    mysqli_close($connect);

    // 댓글 관련 DB 작업이 아닌 경우 목록페이지로 이동합니다.
    if($commentText == NULL){
        // 목록페이지로 이동합니다.
        echo ("<script>
                window.location.href = 'listPage.php'; 
            </script>");
    }
    // 댓글 update 또는 delete일 경우 원래 페이지로 이동
    else if($commentText != NULL && $commentValuePageMove != NULL){
        // 목록페이지로 이동합니다.
        echo ("<script>
                window.location.href = 'updateAndDeletePage.php?boardID=$boardID'; 
            </script>");
    }
}
// DB 접속에 실패할 경우 else문 실행
else {
    echo "DB접속에 실패하였습니다.";
}
?>