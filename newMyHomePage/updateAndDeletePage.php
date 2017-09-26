<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>updateAndDeletePage 1401228 이준영</title>
</head>
<style>
    /*전체 테이블*/
    #readTable{
        width: 500px;
        /*height: 600px;*/
        background-color: #f5ffc1;
        text-align: center;
        border: dotted;
    }

    /*글 정보가 있는 td*/
    #writeInfo{
        width: 500px;
        height: 30px;
    }

    /*제목이 있는 text*/
    #titleText{
        width: 320px;
        height: 30px;
        font-size: 20px;
        background-color: white;
    }

    /*글이 있는 textarea*/
    #contentsText{
        width: 370px;
        height: 400px;
        font-size: 20px;
        background-color: white;
    }

    /*댓글 창에 대한 class*/
    .commentText{
        width: 400px;
        height: 100px;
    }

    .readButton{
        width: 100px;
        height: 50px;
        font-size: 20px;
        background-color: black;
        color: white;
    }
</style>
<?php
/**
 * Created by PhpStorm.
 * User: hello
 * Date: 2017-09-13
 * Time: 오후 12:21
 *
 * 작성한 글을 읽어 오는 페이지
 */

// 로그인 여부를 확인하는 세션 값, 1 -> 로그인, 0 -> 로그인 아님
if(isset($_SESSION['loginState'])){
    $loginValue = $_SESSION['loginState'];
} else
    $loginValue = 0;

// 해당 작업을 수행하는 사용자의 아이디를 받습니다.
if(isset($_SESSION['userID'])){
    $userID = $_SESSION['userID'];
} else
    $userID = "tempID";

// JSON형식의 문자열로 변환하여 저장합니다.
$userID = json_encode($userID);

// 수정해야 하는 글 번호
$boardID = $_POST['boardID'];
// 수정해야 하는 글 번호의 자료형을 int형으로 변환합니다.
settype($boardID,"integer");

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
    // 조회수를 1 증가시키는 SQL문
    $hitsUpdateSQL = "UPDATE $tableName SET hits = hits+1 WHERE board_id = $boardID";
    // 조회수를 1증가시키는 SQL문을 실행합니다.
    $hitsUpdateQuery = mysqli_query($connect, $hitsUpdateSQL);

    // 해당 글의 정보를 조회하는 SQL문
    $writeInfoSelectSQL =
        "SELECT board_id, user_id, user_grade, title, contents, hits, reg_date
        FROM $tableName WHERE board_id = $boardID";
    // 해당 글의 정보를 조회하는 SQL문을 실행합니다.
    $writeInfoSelectQuery = mysqli_query($connect, $writeInfoSelectSQL);
    // 조회한 정보를 가져옵니다.
    $writeInfoSelectData = mysqli_fetch_array($writeInfoSelectQuery);
    // 작성자 값을 JSON형식의 문자열로 변환하여 저장합니다.
    $writer = json_encode($writeInfoSelectData[1]);
    // 글 내용에 해당하는 $writeInfoSelectData[4]에 저장된 문자열에 존재하는 태그를 제거합니다.
    $writeInfoSelectData[4] = strip_tags($writeInfoSelectData[4]);
}

/*
 * 글 번호로 SQL문을 활용하여 해당 글의 정보를 찾고
 * 찾은 글의 정보를 제목,내용, 등등 html 태그에 저장하여 출력
 * 수정 삭제 취소 버튼 만들기 (수정, 삭제는 로그인 및 작성자와 일치하는지 확인하기)
 * 수정 -> 수정한 정보를 setDB.php로 보내어 저장
 * 삭제 -> 삭제할 글 번호를 setDB.php로 보내어 삭제
 * 취소 -> listPage.php로 이동
 *
 * */

echo
"<div>
    <form method='post' action='setDB.php' id='updateAndDeleteForm'>
        <table border='1' id='readTable'>
            <tr>
                <!--글 제목-->
                <td colspan='5'>
                    제목 : <input type='text' id='titleText' name='titleText' 
                    value='$writeInfoSelectData[3]' disabled='disabled'>
                </td>
            </tr>
            <tr id='writeInfo'>
                <!--글 번호-->
                <td>번호 : $writeInfoSelectData[0]</td>
                <!--유저명-->
                <td>아이디 : $writeInfoSelectData[1]</td>
                <!--등급-->
                <td>등급 : $writeInfoSelectData[2]</td>
                <!--조회수-->
                <td>조회수 : $writeInfoSelectData[5]</td>
                <!--등록 날짜-->
                <td>등록 날짜 : $writeInfoSelectData[6]</td>
            </tr>
            <tr>
                <!--글 내용-->
                <td colspan='5'>
                    <textarea id='contentsText' name='contentsText' disabled='disabled'>$writeInfoSelectData[4]</textarea>
                </td>
            </tr>
            <tr>
                <td colspan='5'>
                    <!--수정 버튼, 수정된 제목, 수정된 내용, 글 번호 값 전달필요-->
                    <input type='button' value='수정' class='readButton' id='prtUpdateButton' onclick='prtUpdateButClick()'>
                    <!--확인 버튼, 수정된 제목, 수정된 내용, 글 번호 값 전달필요-->
                    <input type='hidden' value='확인' class='readButton' id='updateButton' onclick='updateButClick()'>
                    <!--삭제 버튼, 글 번호, 삭제 작업 여부(삭제 작업 -> 1, 삭제 작업이 아니면 -> 0) 전달 필요-->
                    <input type='hidden' value='삭제' class='readButton' id='deleteButton' onclick='deleteButClick()'>
                    <!--목록 버튼-->
                    <input type='button' value='목록' class='readButton' id='cancelButton' onclick='cancelButClick()'>
                </td>
            </tr><br><br>
            <tr>
                <!--댓글 form-->
                <form method='post' action='setDB.php' id='commentForm'>
                    <!--댓글 textarea 창-->
                    <td colspan='4' class='commentText'><textarea class='commentText' name='commentText'></textarea></td>
                    <!--댓글 버튼-->
                    <td id='commentButtonTd'>
                        <input type='button' value='댓글' class='readButton' onclick='commentButClick()'>
                    </td>
                    <!--글 번호-->
                    <input type='hidden' value='$writeInfoSelectData[0]' name='boardID'>
                </form>
            </tr>
        </table>
        <!--글 번호-->
        <input type='hidden' name='boardID' value='$writeInfoSelectData[0]'>
        <!--삭제 여부-->
        <input type='hidden' name='deleteCheck' id='deleteCheck' value='0'>
    </form>
</div>";


?>
<script language="JavaScript">
    // 취소 버튼을 누르면 실행되는 함수
    function cancelButClick() {
        // 리스트 페이지로 이동합니다.
        window.location.href = "listPage.php";
    }

    // 수정 버튼을 눌러 수정 작업을 실행 할 수 있도록 하는 함수
    function prtUpdateButClick() {
        // 로그인 상태이면서 해당 글의 작성자와 동일한 사용자인지 확인합니다.
        if(<?=$loginValue?> && <?=$userID?> == <?=$writer?>){
            // 수정 준비 버튼 (수정 작업을 할 수 있도록 하는 버튼)의 type을 hidden으로 변경합니다.
            document.getElementById("prtUpdateButton").type = "hidden";
            // 수정 버튼의 type을 button으로 변경합니다.
            document.getElementById("updateButton").type = "button";
            // 삭제 버튼의 type을 button으로 변경합니다.
            document.getElementById("deleteButton").type = "button";
            /*// 제목 값을 출력하는 div를 안보이게 합니다.
            document.getElementById("titleDiv").style.display = "none";*/
            // 제목 텍스트 창의 type을 변경합니다.
            document.getElementById("titleText").disabled = false;
            // 글 내용 textarea의 disabled 속성을 false로 변경하여
            // textarea에 작성이 가능하도록 변경합니다.
            document.getElementById("contentsText").disabled = false;
        } else{
            alert("사용자가 일치하지 않습니다!!\nへ(￣∇￣へ)");
        }
    }

    // 수정 작업을 하는 확인 버튼을 누르면 실행되는 함수
    function updateButClick() {
        // 로그인 상태이면서 해당 글의 작성자와 동일한 사용자인지 확인합니다.
        if(<?=$loginValue?> && <?=$userID?> == <?=$writer?>){
            // submit()함수를 호출하여 이동합니다.
            document.getElementById("updateAndDeleteForm").submit();
        } else{
            alert("사용자가 일치하지 않습니다!!\nへ(￣∇￣へ)");
        }
    }

    // 삭제 버튼을 누르면 실행되는 함수
    function deleteButClick() {
        // 로그인 상태이면서 해당 글의 작성자와 동일한 사용자인지 확인합니다.
        if(<?=$loginValue?> && <?=$userID?> == <?=$writer?>){
            // 삭제 여부를 나타내는 값을 삭제를 뜻하는 1로 변경합니다.
            document.getElementById("deleteCheck").value = 1;
            // submit()함수를 호출하여 이동합니다.
            document.getElementById("updateAndDeleteForm").submit();
        } else{
            alert("사용자가 일치하지 않습니다!!\nへ(￣∇￣へ)");
        }
    }

    // 댓글 버튼을 누르면 실행되는 함수
    function commentButClick() {
        // 로그인 상태일 경우
        if(<?=$loginValue?> != 0){
            document.getElementById("commentForm").submit();
        }
        // 로그아웃 상태일 경우
    else {
            alert("로그인 상태가 아님니다!!\nへ(￣∇￣へ)");
        }
    }
</script>
</html>