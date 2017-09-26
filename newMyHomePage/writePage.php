<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>writePage 1401228 이준영</title>
</head>
<style>
    #backPage{
        width: 500px;
        height: 600px;
        background-color: #c8ff58;
        text-align: center;
        border: dotted;
    }

    #titleText{
        width: 320px;
        height: 30px;
        font-size: 20px;
    }

    #contentsText{
        width: 370px;
        height: 400px;
        font-size: 20px;
    }

    .writeButton{
        width: 100px;
        height: 50px;
        font-size: 20px;
        background-color: black;
        color: white;
    }
</style>
<div id="backPage"><br>
    <form method="post" id="buttonChoice">
        제목 : <input type="text" id="titleText" name="titleText"><br><br>
        <div>글 내용</div>
        <textarea id="contentsText" name="contentsText"></textarea><br>
        <input type="button" value="확인" id="okButton" class="writeButton" onclick="writeOk()">
        <input type="button" value="취소" id="goList" class="writeButton" onclick="writeCancel()">
        <input type="hidden" id="boardID" value="" name="boardID">
    </form>
</div>
<body>
<?php
/**
 * Created by PhpStorm.
 * User: hello
 * Date: 2017-09-13
 * Time: 오전 12:09
 *
 * 글쓰기 페이지
 */

?>
<script language="JavaScript">
    // 작성 한 글을 등록하는 함수
    function writeOk() {
        // 제목 값 엘리먼트
        var titleValue = document.getElementById("titleText");

        // 제목 값이 빈값이 아닐 경우 if문을 실행합니다.
        if(titleValue.value != "") {
            document.getElementById("buttonChoice").action = "setDB.php";
            document.getElementById("buttonChoice").submit();
        }
        else{
            alert("제목이 없습니다!!");
        }
    }

    // 작성 한 글을 취소하는 함수
    function writeCancel() {
        document.getElementById("buttonChoice").action = "listPage.php";
        document.getElementById("buttonChoice").submit();
    }
</script>
</body>
</html>