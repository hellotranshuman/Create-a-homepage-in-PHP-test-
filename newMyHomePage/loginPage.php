<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
</head>
<style>
    #loginTable {
        width: 300px;
        height: 170px;
        text-align: center;
        border: 1px dashed black;
    }
</style>
<body>
<form method="post" id="loginForm">
    <table id="loginTable" border="1">
        <tr>
            <td colspan="2">로그인</td>
        </tr>
        <tr>
            <td>아이디 : </td>
            <td><input type="text" name="inputID"></td>
        </tr>
        <tr>
            <td>비밀번호 : </td>
            <td><input type="password" name="inputPasswd"></td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="button" value="확인" onclick="goLoginData()">
                <input type="button" value="취소" onclick="loginCancel()">
            </td>
        </tr>
    </table>
</form>
</body>
<script language="JavaScript">
    // 로그인 정보에 대한 form 엘리먼트 객체
    loginFormElement = document.getElementById("loginForm");

    // 확인 버튼을 누르면 실행되는 함수
    function goLoginData() {
        loginFormElement.action = "loginDB.php";
        loginFormElement.submit();
    }

    // 취소 버튼을 누르면 실행되는 함수
    function loginCancel() {
        loginFormElement.action = "home.php";
        loginFormElement.submit();
    }
</script>
<?php
/**
 * Created by PhpStorm.
 * User: hello
 * Date: 2017-09-12
 * Time: 오후 11:42
 *
 * 로그인 페이지
 */
?>
</html>