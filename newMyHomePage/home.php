<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>1401228 이준영 홈페이지 Home</title>
</head>
<style>
    #homeMainTable{
        width: 300px;
        height: 250px;
        text-align: center;
        background-color: #d6fcff;
        border: 1px dashed black;
    }

    .homeButton{
        width: 100px;
        height: 50px;
        font-size: 20px;
        background-color: black;
        color: white;
    }
</style>
<body>
<table id="homeMainTable">
    <tr>
        <td colspan="2">1401228 이준영</td>
    </tr>
    <tr>
        <form method="post" action="loginPage.php" id="goLoginForm">
            <td><input type="button" value="로그인" id="login" class="homeButton" onclick="loginCheck(event)"></td>
        </form>
    </tr>
    <tr>
        <form method="post" action="writePage.php" id="goWriteForm">
            <td><input type="button" value="글 작성" id="write" class="homeButton" onclick="writeQualifyCheck(event)"></td>
        </form>
    </tr>
    <tr>
        <form method="post" action="listPage.php">
            <td><input type="submit" value="목록" id="list" class="homeButton"></td>
        </form>
    </tr>
</table>
</body>
<?php
/**
 * Created by PhpStorm.
 * User: hello
 * Date: 2017-09-12
 * Time: 오후 3:33
 */

// 로그인 상태를 나타내는 $_SESSION['loginState']의 값이 존재할 경우
// 로그인 여부를 검사합니다.
if(isset($_SESSION['loginState'])){
    // $_SESSION['loginState']의 값을 $loginValue에 저장합니다.
    $loginValue = $_SESSION['loginState'];
}
else
    // $_SESSION['loginState']의 값이 존재하지 않을 경우 로그인이 안되었다는
    // 것이므로 $loginValue을 0으로 초기화합니다.
    $loginValue = 0;

?>
<script language="JavaScript">
    /*
    * 로그인 버튼을 누르면 -> 로그인 여부를 확인
    * php에서 $_SESSION['loginState']의 값을 받아
    * 1 -> 로그인 중, 0 -> 로그인 아님
    *
    * 로그인 중일 때
    * -> 경고창 (로그인 하였습니다.)
    *
    * 로그인 아닐 때
    * -> loginPage.php로 이동
    * */

    // 로그인 버튼을 눌렸을 때 실행
    function loginCheck() {
        // 로그인 중 일 때
        if(<?=$loginValue?> == 1){
            alert("이미 로그인 하였습니다!!\nへ(￣∇￣へ)");
        }
        // 로그인 중이 아닐 때는 페이지를 이동합니다.
        else{
            document.getElementById("goLoginForm").submit();
        }
    }
    /********************************************* loginCheck()함수 끝 *********************************************/

    // 글 작성 버튼을 눌렸을 때 실행
    // 글 작성 권한이 있는 지(로그인 했는지)를 확인하는 함수
    function writeQualifyCheck() {
        // 로그인 중 일 때
        if(<?=$loginValue?> == 1){
            document.getElementById("goWriteForm").submit();
        }
        // 로그인 중이 아닐 때는 페이지를 이동합니다.
        else{
            alert("로그인 해주세요!!\nへ(￣∇￣へ)");
        }
    }
    /********************************************* writeQualifyCheck()함수 끝 *********************************************/

</script>
</html>