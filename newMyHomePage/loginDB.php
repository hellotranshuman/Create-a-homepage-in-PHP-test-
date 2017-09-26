<?php
/**
 * Created by PhpStorm.
 * User: hello
 * Date: 2017-09-18
 * Time: 오전 9:40
 */

// 사용자로 부터 입력 받은 아이디
if(isset($_POST['inputID'])){
    $inputID = $_POST['inputID'];
}

// 사용자로 부터 입력 받은 비밀번호
if(isset($_POST['inputPasswd'])){
    $inputPasswd = $_POST['inputPasswd'];
}

// 로그아웃인지를 구분하는 값
// 1 -> 로그아웃 실행 0 -> 로그아웃을 실행하지 않습니다.
if(isset($_POST["logOut"])){
    $logOutCheck = $_POST["logOut"];

    // 값을 int형으로 변환합니다.
    settype($logOutCheck, "integer");
}
// 로그아웃인지 구분하는 $_POST["logOut"]값을 전달받지 못한 경우
else {
    $logOutCheck = 0;
}

//MySQL 서버 주소
define("HOST","localhost");
//DB 사용자 계정
define("USER","newBoardUser");
//DB 비밀번호 -> eP9DW8a92e2wxQhE
define("DBPASSWD","eP9DW8a92e2wxQhE");
//DB 이름
define("DBNAME","new_bulletin_board");
//글 정보가 들어 있는 테이블 명
$tableName = "userinfo";

// DB에 연결합니다.
$connect = mysqli_connect(HOST, USER, DBPASSWD, DBNAME);

// DB연결에 성공하였을 경우
if($connect){
    // $logOutCheck의 값이 1일 경우 세션을 제거하여 로그아웃을 진행합니다.
    if($logOutCheck == 1){
        // 세션을 제거합니다.
        session_destroy();

        // listPage.php 페이지로 이동합니다.
        echo("<script>
                window.location.href = 'listPage.php';
                </script>");
    } else {
        // DB에서 입력받은 아이디 및 비밀번호와 일치하는 데이터가 있는지 확인하는 SQL문
        $idAndPasswdSelectSQL =
            "SELECT * FROM $tableName
        WHERE userID = '$inputID' && userPasswd = '$inputPasswd'";

        // $idAndPasswdSelectSQL에 저장된 쿼리문을 실행합니다.
        $idAndPasswdCheck = mysqli_query($connect, $idAndPasswdSelectSQL);

        $idAndPasswdCheckResult = mysqli_fetch_array($idAndPasswdCheck);

        // DB접속을 종료합니다.
        mysqli_close($connect);

        // 아이디와 비밀번호 모두가 일치하는 경우
        if ($idAndPasswdCheckResult[1] == $inputID &&
            $idAndPasswdCheckResult[2] == $inputPasswd
        ) {

            echo "login";

            // sesstionID를 부여합니다.
            @session_start();

            // 로그인 상태를 나타내는 $_SESSION['loginState']의 값을 1로 초기화 합니다.
            // 1 -> 로그인, 0 -> 로그인 아님
            $_SESSION['loginState'] = 1;
            // $_SESSION['userID']에 유저 아이디를 저장합니다.
            $_SESSION['userID'] = $idAndPasswdCheckResult[1];
            // $_SESSION['nickname']에 회원 등급을 저장합니다.
            $_SESSION['userGrade'] = $idAndPasswdCheckResult[3];


            echo("<script>
                window.location.href = 'home.php';
                </script>");
        } // 아이디와 비밀번호 모두 일치하는 데이터가 없을 경우
        else {
            echo("<script>
                alert('일치하지 않습니다. 다시 입력 해주세요!!');
                window.location.href = 'loginPage.php';
                </script>");
        }
    }
}
// DB 접속에 실패할 경우 else문 실행
else {
    echo "DB접속에 실패하였습니다.";
}
?>