<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>updateAndDeletePage 1401228 이준영</title>
</head>
<style>
    /*전체 테이블*/
    .readTable {
        width: 600px;
        /*height: 600px;*/
        background-color: #f5ffc1;
        text-align: center;
        border: dotted;
    }

    /*글 정보가 있는 td*/
    #writeInfo {
        width: 500px;
        height: 30px;
    }

    /*제목이 있는 text*/
    #titleText {
        width: 320px;
        height: 30px;
        font-size: 20px;
        background-color: white;
    }

    /*글이 있는 textarea*/
    #contentsText {
        width: 370px;
        height: 400px;
        font-size: 20px;
        background-color: white;
    }

    /*댓글 창에 대한 class*/
    .commentText {
        width: 400px;
        height: 100px;
    }

    .readButton {
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
if (isset($_SESSION['loginState'])) {
    $loginValue = $_SESSION['loginState'];
} else
    $loginValue = 0;

// 해당 작업을 수행하는 사용자의 아이디를 받습니다.
if (isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];
} else
    $userID = "tempID";

// JSON형식의 문자열로 변환하여 저장합니다.
@$userID = json_encode($userID);

// 수정해야 하는 글 번호
@$boardID = $_GET['boardID'];
// 수정해야 하는 글 번호의 자료형을 int형으로 변환합니다.
settype($boardID, "integer");

//MySQL 서버 주소
define("HOST", "localhost");
//DB 사용자 계정
define("USER", "newBoardUser");
//DB 비밀번호 -> eP9DW8a92e2wxQhE
define("DBPASSWD", "eP9DW8a92e2wxQhE");
//DB 이름
define("DBNAME", "new_bulletin_board");
//글 정보가 들어 있는 테이블 명
$tableName = "boarddata";

// DB에 연결합니다.
$connect = mysqli_connect(HOST, USER, DBPASSWD, DBNAME);

// DB연결에 성공하였을 경우
if ($connect) {
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

    // DB에서 해당 글의 board_id와 같은 board_pid를 가진 값의
    // 유저 아이디, 회원 등급, 내용, 날짜를 SELECT하는 SQL문 (댓글)
    $commentSelectSQL =
        "SELECT board_id, user_id, user_grade, contents, reg_date
         FROM $tableName
         WHERE board_pid = $boardID";

    // 이 글의 댓글에 해당하는 글의 정보를 SELECT하는 SQL문을 실행합니다.
    $commentSelectQuery = mysqli_query($connect, $commentSelectSQL);

    // 저장되어 있는 댓글값의 최대 숫자
    $commentCountMax = mysqli_num_rows($commentSelectQuery);

    // board_id의 최대값을 구하는 sql문
    $maxBoardSQL = "SELECT MAX(board_id) FROM $tableName";
    // board_id의 최대값을 구하는 sql문을 실행합니다.
    $maxBoardQuery = mysqli_query($connect, $maxBoardSQL);
    // 실행한 board_id의 최대값을 구하는 sql문에서 값을 가져옵니다.
    $maxBoardArr = mysqli_fetch_array($maxBoardQuery);
    // board_id의 최대값을 변수에 저장합니다.
    $maxBoardValue = $maxBoardArr[0];
    // 구해진 board_id의 최대값의 값을 1증가시킵니다.
    // board_id의 최대값에서 1을 증가해야지 새로운 board_id를 나타낼 수 있기 때문입니다.
    $maxBoardValue++;

    /*
    * 글 번호로 SQL문을 활용하여 해당 글의 정보를 찾고
    * 찾은 글의 정보를 제목,내용, 등등 html 태그에 저장하여 출력
    * 수정 삭제 취소 버튼 만들기 (수정, 삭제는 로그인 및 작성자와 일치하는지 확인하기)
    * 수정 -> 수정한 정보를 setDB.php로 보내어 저장
    * 삭제 -> 삭제할 글 번호를 setDB.php로 보내어 삭제
    * 취소 -> listPage.php로 이동
    * */

    echo
    "<table border='1' class='readTable'>
     <form method='post' action='setDB.php' id='updateAndDeleteForm'>
        <tr>
            <!--글 제목-->
            <td colspan='5'>
                제목 : <input type='text' id='titleText' name='titleText' value='$writeInfoSelectData[3]' disabled='disabled'>
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
        <!--글 번호-->
        <input type='hidden' name='boardID' value='$writeInfoSelectData[0]'>
        <!--삭제 여부-->
        <input type='hidden' name='deleteCheck' id='deleteCheck' value='0'>
     </form>
    <!--댓글 form-->
    <form method='post' action='setDB.php' id='commentForm'>
        <tr>
            <!--댓글 textarea 창-->
            <td colspan='4' class='commentText'>
                <textarea class='commentText' name='commentText' id='commentText'></textarea>
            </td>
            <!--댓글 버튼-->
            <td id='commentButtonTd'>
                <input type='button' value='댓글' class='readButton' onclick='commentButClick()'>
            </td>
            <!--글 번호-->
            <input type='hidden' name='boardID' value='$writeInfoSelectData[0]'>
            <!--삭제 여부-->
            <input type='hidden' name='deleteCheck' id='deleteCheck' value='0'>
        </tr>
    </form>
    </table>";

    echo
    "<form method='post' action='setDB.php' id='commentUpAndDelForm'>
        <!--글 번호-->
        <input type='hidden' name='boardID' value='$writeInfoSelectData[0]'>
        <!--댓글 번호-->
        <input type='hidden' id='commentBoardID' name='commentBoardID' value='0'>
        <!--삭제 여부-->
        <input type='hidden' name='deleteCheck' id='commentDeleteCheck' value='0'>
        <!--댓글 값-->
        <input type='hidden' name='commentText' id='commentTextValue' value='1'>
        <!--댓글 update 여부를 체크하는 값-->
        <input type='hidden' name='commentUpdateCheck' id='commentUpdateCheck' value='0'>
        <table border='1' class='readTable' id='commentStartTable'>";

         for ($iCount = 0; $iCount < $commentCountMax; $iCount++) {
             $commentInfo = mysqli_fetch_array($commentSelectQuery);

            // 첫 반복일때만 if문을 실행합니다.
            if ($iCount == 0) {
                echo
                "<tr>
                    <td>아이디</td>
                    <td colspan='2'>댓글</td>
                    <td>등록 날짜</td>
                    <td>수정&삭제</td>
                </tr>";
            }

             // 입력된 내용의 줄바꿈을 적용하기 위해 nl2br()함수를 사용합니다.
             $commentInfo[3] = nl2br($commentInfo[3]);
             // 문자열에 존재하는 태그를 제거합니다.
             $commentInfo[3] = strip_tags($commentInfo[3]);

        echo
        "<tr id='$commentInfo[0]_comment'>
            <td>$commentInfo[1]</td>
            <td colspan='2'>
               <textarea id='$commentInfo[0]_commentText' name='commentTextarea' 
               disabled='disabled'>$commentInfo[3]</textarea>
            </td>
            <td>$commentInfo[4]</td>
            <td>
                <input type='button' value='수정' id='$commentInfo[0]_commentUP' onclick='reComment(event)'>
                <input type='hidden' value='확인' id='$commentInfo[0]_movePageBut' onclick='commentUpdateMovePage(event)'>
                <input type='button' value='삭제' onclick='delComment(event)'>
            </td>
         </tr>";
    }
    echo "
    </table>
    </form>";

    mysqli_close($connect);
}
// DB 접속에 실패할 경우 else문 실행
else {
    echo "DB접속에 실패하였습니다.";
}
?>
<body>
<script language="JavaScript">
    // 취소 버튼을 누르면 실행되는 함수
    function cancelButClick() {
        // 리스트 페이지로 이동합니다.
        window.location.href = "listPage.php";
    }
    /********************************************* cancelButClick() 함수 끝 *********************************************/

    // 수정 버튼을 눌러 수정 작업을 실행 할 수 있도록 하는 함수
    function prtUpdateButClick() {
        // 로그인 상태이면서 해당 글의 작성자와 동일한 사용자인지 확인합니다.
        if (<?=$loginValue?> && <?=$userID?> == <?=$writer?>){
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
        }
        else{
            alert("사용자가 일치하지 않습니다!!\nへ(￣∇￣へ)");
        }
    }
    /********************************************* prtUpdateButClick() 함수 끝 *********************************************/

    // 수정 작업을 하는 확인 버튼을 누르면 실행되는 함수
    function updateButClick() {
        // 로그인 상태이면서 해당 글의 작성자와 동일한 사용자인지 확인합니다.
        if (<?=$loginValue?> && <?=$userID?> == <?=$writer?>){
            // submit()함수를 호출하여 이동합니다.
            document.getElementById("updateAndDeleteForm").submit();
        }
         else{
            alert("사용자가 일치하지 않습니다!!\nへ(￣∇￣へ)");
        }
    }
    /********************************************* updateButClick() 함수 끝 *********************************************/

    // 삭제 버튼을 누르면 실행되는 함수
    function deleteButClick() {
        // 로그인 상태이면서 해당 글의 작성자와 동일한 사용자인지 확인합니다.
        if (<?=$loginValue?> && <?=$userID?> == <?=$writer?>){
            // 삭제 여부를 나타내는 값을 삭제를 뜻하는 1로 변경합니다.
            document.getElementById("deleteCheck").value = 1;
            // submit()함수를 호출하여 이동합니다.
            document.getElementById("updateAndDeleteForm").submit();
        }
    else{
            alert("사용자가 일치하지 않습니다!!\nへ(￣∇￣へ)");
        }
    }
    /********************************************* deleteButClick() 함수 끝 *********************************************/

    // DB에 저장되어 있는 댓글의 ID값
    commentBoardID = <?=$maxBoardValue?>;

    // 댓글 버튼을 누르면 실행되는 함수
    function commentButClick() {
        // 댓글값을 입력하는 textarea 엘리먼트를 저장합니다.
        var commentText = document.getElementById("commentText");

        // 댓글값을 입력하는 textarea 엘리먼트의 값이 있을 경우
        if(commentText.value != ""){
            // 로그인 상태일 경우
            if (<?=$loginValue?> !=0){
                // ajax로 댓글을 작성
                // new XMLHttpRequest()는 현재 웹페이지는 놔두고
                // 새롭게 http요청을 서버로 보내고 응답을 받아 데이터를 획득하는 역할
                var ajaxReqObj = new XMLHttpRequest();

                //onreadystatechange는 이벤트핸들러입니다.
                //서버의 처리 상태의 변화에 따른 이벤트 발생 처리 상태 값을 readyState 프로퍼티로 제공합니다.
                ajaxReqObj.onreadystatechange = function () {
                    //서버 처리상태를 나타내는 readyState의 값이 4일 경우 서버처리가 끝났다는 것이고
                    //서버의 처리 결과를 나타내는 status의 값이 200이라는 것은
                    //서버 처리 결과가 성공했다는 것입니다.
                    if (ajaxReqObj.readyState == 4 && ajaxReqObj.status == 200) {
                        // ajax를 사용하여 반환된 값을 변수에 저장합니다.
                        var getCommentInfo = ajaxReqObj.responseText;

                        // _(언더바)를 기준으로 값을 나누어 배열에 저장합니다.
                        var getCommentInfoArr = getCommentInfo.split("_");

                        // ajax로 받아온 댓글값을 tr,td 속성에 넣어 변수에 저장합니다.
                        var getCommentElement =
                            "<tr id='commentID'>" +
                            "<td>" + getCommentInfoArr[1] + "</td>" +
                            "<td colspan='2'>" +
                            "<textarea id='commentTextarea' name='commentTextarea' disabled='disabled'>" +
                            getCommentInfoArr[3] + "</textarea>" +
                            "</td>" +
                            "<td>" + getCommentInfoArr[4] + "</td>" +
                            "<td>" + "<input type='button' value='수정' id='commentTextBut' onclick='reComment(event)'>" +
                            "<input type='hidden' value='확인' id='movePageBut' onclick='commentUpdateMovePage(event)'>" +
                            "&nbsp;" + "<input type='button' value='삭제' onclick='delComment(event)'>" + "</td>" +
                            "</tr>";

                        // 댓글을 포함하는 테이블에 ajax로 받아온 댓글을 추가합니다.
                        document.getElementById("commentStartTable").innerHTML += getCommentElement;

                        // commentID를 id로 가지는 tr엘리먼트를 저장합니다.
                        var trElement = document.getElementById("commentID");
                        // tr에 부여할 id속성 값을 변수에 저장합니다.
                        var commentID = getCommentInfoArr[0] + "_comment";
                        // tr에 id속성을 부여합니다.
                        trElement.setAttribute("id", commentID);

                        // commentID를 id로 가지는 textarea엘리먼트를 저장합니다.
                        var commentTextArea = document.getElementById("commentTextarea");
                        // textarea에 부여할 id속성 값을 변수에 저장합니다.
                        var commentTextAreaID = getCommentInfoArr[0] + "_commentText";
                        // textarea에 id속성을 부여합니다.
                        commentTextArea.setAttribute("id", commentTextAreaID);

                        // commentID를 id로 가지는 button엘리먼트를 저장합니다.
                        var commentUpBut = document.getElementById("commentTextBut");
                        // button에 부여할 id속성 값을 변수에 저장합니다.
                        var commentUpButID = getCommentInfoArr[0] + "_commentUP";
                        // button에 id속성을 부여합니다.
                        commentUpBut.setAttribute("id", commentUpButID);

                        // movePageBut를 id로 가지는 button엘리먼트를 저장합니다.
                        var movePageBut = document.getElementById("movePageBut");
                        // button에 부여할 id속성 값을 변수에 저장합니다.
                        var movePageButID = getCommentInfoArr[0] + "_movePageBut";
                        // button에 id속성을 부여합니다.
                        movePageBut.setAttribute("id", movePageButID);

                        // 입력이 완료되었으므로 commentText를 id로 가지는 textarea의 값을 빈값으로 초기화합니다.
                        document.getElementById("commentText").value = "";
                    }
                }

                //데이터를 전송할 url명을 저장합니다.
                var url = "setDB.php";
                //접속할 url 및 접속 방식을 기입
                ajaxReqObj.open('POST', url, true);

                //서버로 전송할 데이터 타입의 형식(MIME)을 지정한다.
                ajaxReqObj.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");

                //php파일에 보낼 데이터를 초기화합니다.
                var data = "commentText=" + document.getElementById("commentText").value +
                    "&boardID=" + <?=$writeInfoSelectData[0]?> +
                        "&deleteCheck=" + 0 +
                    "&commentBoardID=" + (commentBoardID++);

                //데이터를 전송합니다.
                ajaxReqObj.send(data);
            }

            // 로그아웃 상태일 경우
        else{
                alert("로그인 상태가 아님니다!!\nへ(￣∇￣へ)");
            }
        }
        // 댓글값이 없을 경우
        else
            alert("댓글을 입력해주세요!!\nへ(￣∇￣へ)");
    }
    /********************************************* commentButClick() 함수 끝 *********************************************/

    // 댓글 수정 버튼을 누르면 실행되는 함수
    function reComment(event) {
        /*
         * setDB.php에 값을 보냄 : 댓글 값, 글번호, 삭제 여부 값,
         * 보낸 값으로 delete SQL문을 실행
         * 새로 고침으로 댓글이 삭제된 것이 반영되도록 함
         */

        // 로그인 상태이면서 해당 글의 작성자와 동일한 사용자인지 확인합니다.
        if (<?=$loginValue?> && <?=$userID?> == <?=$writer?>){
            // 수정 버튼의 id를 _를 기준으로 나누어 배열에 저장합니다.
            var updateButID = event.target.id.split("_");

            // textarea의 ID에 해당하는 값을 저장합니다.
            var thisCommentTextID = updateButID[0] + "_commentText";

            // 글 내용 textarea의 disabled 속성을 false로 변경하여 textarea에 작성이 가능하도록 변경합니다.
            document.getElementById(thisCommentTextID).disabled = false;

            // 수정 준비 버튼 (수정 작업을 할 수 있도록 하는 버튼)의 type을 hidden으로 변경합니다.
            document.getElementById(event.target.id).type = "hidden";

            // 수정을 실행하는 확인 버튼의 ID를 구합니다.
            var movePageButID = updateButID[0] + "_movePageBut";
            // 수정을 실행하는 확인 버튼의 type을 button으로 변경합니다.
            document.getElementById(movePageButID).type = "button";
        }
        else{
            alert("사용자가 일치하지 않습니다!!\nへ(￣∇￣へ)");
        }
    }
    /********************************************* reComment(event) 함수 끝 *********************************************/

    function commentUpdateMovePage(event) {
        // 이 함수를 호출한 button의 id를 _를 기준으로 나누어 배열에 저장합니다.
        var movePageButArr = event.target.id.split("_");

        document.getElementById("commentUpdateCheck").value = "1";

        // commentTextValue을 id로 가지는 hidden값에 현재의 댓글 값을 대입합니다.
        document.getElementById("commentTextValue").value = document.getElementById(movePageButArr[0] + "_commentText").value;

        // boardID을 id로 가지는 hidden엘리먼트에 DB상의 해당 댓글에 해당하는 id값을 대입합니다.
        document.getElementById("commentBoardID").value = movePageButArr[0];

        // submit()함수를 호출하여 이동합니다.
         document.getElementById("commentUpAndDelForm").submit();
    }
    /********************************************* commentUpdateMovePage(event) 함수 끝 *********************************************/

    // 댓글 삭제 버튼을 누르면 실행되는 함수
    function delComment(event) {
        // 로그인 상태이면서 해당 글의 작성자와 동일한 사용자인지 확인합니다.
        if (<?=$loginValue?> && <?=$userID?> == <?=$writer?>){
            // 삭제 여부를 나타내는 값을 삭제를 뜻하는 1로 변경합니다.
            document.getElementById("commentDeleteCheck").value = 1;
            // 삭제 버튼의 부모의 부모 엘리먼트에 해당하는 tr엘리먼트의 id를 구합니다.
            var commentBoardIDValue = event.target.parentNode.parentNode.id;
            // 구한 tr엘리먼트의 id를 _로 구분하여 나눕니다.
            var commentBoardIDArr = commentBoardIDValue.split("_");
            // 나누어 배열로 저장한 tr엘리먼트의 id 값의 첫번째 값을 저장합니다.
            // (해당 댓글의 DB상의 id값과 동일한 값이 저장되어 있습니다.)
            commentBoardIDValue = commentBoardIDArr[0];
            // commentBoardID를 id로 가지는 엘리먼트의 value에 commentBoardIDValue를 대입합니다.
            document.getElementById("commentBoardID").value = commentBoardIDValue;

            // submit()함수를 호출하여 이동합니다.
            document.getElementById("commentUpAndDelForm").submit();
        }
    else{
            alert("사용자가 일치하지 않습니다!!\nへ(￣∇￣へ)");
        }
    }
    /********************************************* delComment(event) 함수 끝 *********************************************/

</script>
</body>
</html>
