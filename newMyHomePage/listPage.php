<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>페이지</title>
</head>
<style>
    #screenCenter{
        position: absolute;
        top:10%;
        left: 10%;
        width: 1000px;
    }

    /*리스트를 나타내는 테이블 스타일*/
    #listTable{
        width: 1000px;
        border-collapse: separate;
        border-spacing: 0px;
        text-align: center;
    }

    /*각 열별 이름*/
    #titleName{
        background-color: #ecd82e;
        color: #fffff4;
        font-weight: bolder;
    }

    /*글 번호 스타일*/
    #boardNumTd{
        width: 100px;
    }

    /*글 제목 스타일*/
    #titleTd{
        width: 350px;
    }

    /*아이디 스타일*/
    #idTd{
        width: 150px;
    }

    /*등급 스타일*/
    #gradeTd{
        width: 80px;
    }

    /*조회수 스타일*/
    #hitsTd{
        width: 120px;
    }

    /*등록날짜 스타일*/
    #dateTd{
        width: 200px;
    }

    /*페이지 하단바*/
    #pageBar{
        text-align: center;
    }

    b:hover{
        text-decoration: underline;
        color: #ff6e35;
        cursor: default;
    }

    /*각 글의 제목항목 스타일*/
    .titleTdStyle{
        cursor: default;
    }
</style>
<body>
<?php
/**
 * Created by PhpStorm.
 * User: hello
 * Date: 2017-09-12
 * Time: 오후 11:42
 */

/*
 * 리스트페이지
 *
 * ● 현재 페이지 번호
 * --> DB에 현재 페이지 번호 정보 필요
 *
 * ● 검색결과 페이지네이션
 * --> DB에 검색하고 자 하는 값 정보 필요
 *
 * ● 검색조건(작성자, 제목 ,내용, 제목+내용)
 * --> DB에 검색 조건 정보 필요
 *
 * ● 페이지 당 개수 (5, 10, 15, 20)
 * --> DB에 정렬 개수 정보 필요
 *
 * */

// 로그인 여부를 확인하는 세션 값, 1 -> 로그인, 0 -> 로그인 아님
if(isset($_SESSION['loginState'])){
    $loginValue = $_SESSION['loginState'];
} else{
    $loginValue = 0;
}

// 현재 페이지 번호, listPage.php에서 다른 페이지로 이동할 경우 -> 현재 페이지 번호, 그 이외 -> 첫페이지
if(isset($_POST['nowPageNum'])){
    $nowPageNum = $_POST['nowPageNum'];
} else{
    $nowPageNum = 1;
}

// 화면에 출력할 글의 수, (5, 10(기본값), 15, 20) listPage.php에서 이동했을 경우 -> 현재 출력 개수, 그 이외 -> 10(기본값),
if(isset($_POST['prtContentsNum'])){
    $prtContentsNum = $_POST['prtContentsNum'];
} else{
    $prtContentsNum = 10;
}

// 검색조건 (제목 0(기본값), 내용 1, 제목+내용 2) (listPage.php에서 listPage.php로 이동하여 검색 조건에 맞게 페이지네이션)
// listPage.php에서 다른 페이지로 이동을 해도 검색 중일 경우 검색 조건은 유지 되어야 한다.
if(isset($_POST['searchCheck'])){
    $searchCheck = $_POST['searchCheck'];

    // 자료형을 int형으로 변환합니다.
    settype($searchCheck, "integer");
} else{
    // 제목으로 검색을 기본값으로 한다.
    $searchCheck = 0;
}

// 검색 내용 (listPage.php에서 listPage.php로 이동하여 검색 내용에 맞게 페이지네이션)
// listPage.php에서 다른 페이지로 이동을 해도 검색 중일 경우 검색 내용은 유지 되어야 한다.
if(isset($_POST['searchStr'])){
    $searchStr = $_POST['searchStr'];
    // 검색 내용 값을 전달하기 위해 $searchStrJSON 변수에 JSON형식의 문자열로 변환하여 저장합니다.
    $searchStrJSON = json_encode($searchStr);
} else {
    $searchStr = "";
    // 검색 내용 값을 전달하기 위해 $searchStrJSON 변수에 JSON형식의 문자열로 변환하여 저장합니다.
    $searchStrJSON = json_encode($searchStr);
}

/*
 * 1. DB에 접근하여 페이지 출력
 * 2. 페이지네이션 (일반상태, 검색상태, 글 출력개수 조절도 가능)
 * 3. 검색 구현
 * */

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
    // 검색 내용 변수가 빈값이 아닌 경우, 즉 검색을 하는 경우
    // 검색조건 (제목 0(기본값), 내용 1, 제목+내용 2)
    if($searchStr != ""){
        switch($searchCheck){
            // 제목에 검색 내용과 일치하는 내용이 있을 경우
            case 0:
                $searchWHERE = "WHERE (title LIKE '%$searchStr%') AND (board_pid = 0)";
                break;
            // 글에 검색 내용과 일치하는 내용이 있을 경우
            case 1:
                $searchWHERE = "WHERE (contents LIKE '%$searchStr%') AND (board_pid = 0)";
                break;
            // 제목 또는 글에 검색 내용과 일치하는 내용이 있을 경우
            case 2:
                $searchWHERE = "WHERE ((title LIKE '%$searchStr%') OR (contents LIKE '%$searchStr%')) AND (board_pid = 0)";
                break;
        }

        // 검색 내용 및 검색 조건에 따라 알맞은 게시판의 글을 select하는 SQL문
        $boardDataSelectSQL =
            "SELECT R.*
            FROM 
            (SELECT board_id, user_id, user_grade, title, hits, reg_date, @ROWNUM := @ROWNUM + 1 AS ROWNUM
            FROM $tableName, (SELECT @ROWNUM := 0) AS ROW
            $searchWHERE
            ORDER BY reg_date DESC) R
            WHERE R.ROWNUM BETWEEN ($prtContentsNum*($nowPageNum -1) + 1) AND ($prtContentsNum*$nowPageNum);";
    } else{
        // 게시판의 모든 글을 select하는 SQL문
        $boardDataSelectSQL =
           "SELECT R.*
            FROM 
            (SELECT board_id, user_id, user_grade, title, hits, reg_date, @ROWNUM := @ROWNUM + 1 AS ROWNUM
            FROM $tableName, (SELECT @ROWNUM := 0) AS ROW
            WHERE board_pid = 0
            ORDER BY reg_date DESC) R
            WHERE R.ROWNUM BETWEEN ($prtContentsNum*($nowPageNum -1) + 1) AND ($prtContentsNum*$nowPageNum);";
    }

    // 게시판의 정보를 select하는 SQL문을 실행합니다.
    $boardSelectQuery = mysqli_query($connect, $boardDataSelectSQL);

    ////////////////////////////////////////////////////////////////////////////////////////////////////////
    if(!$boardSelectQuery){
        echo " ,DB실패";
    } else
        echo " ,DB성공";


    // 수정을 하기 위해 writePage.php로 이동을 할때 값을 전송하기 위해 사용되는 form태그
    echo
    "<form method='get' action='updateAndDeletePage.php' id='updateForm'>
        <!--글 번호-->
        <input type='hidden' name='boardID' id='boardID'>
    </form>";

    // html 태그로 리스트를 출력
    echo "<div id='screenCenter'>";
    echo
    "<table>
        <tr>
            <td>
                <form method='post' action='listPage.php' id='rePage'>
                    <!--검색조건 (제목 0(기본값), 내용 1, 제목+내용 2)-->
                    <select name='searchCheck' id='searchCheck'>
                        <option value = '0' name='searchOptionValue' id='titleSearch'>제목 검색</option>
                        <option value = '1' name='searchOptionValue' id='contentsSearch'>내용 검색</option>
                        <option value = '2' name='searchOptionValue' id='titleAndContentsSearch'>제목+내용 검색</option>
                    </select>
                    <!--검색 창-->
                    <input type='text' name='searchStr' id='searchStr'>
                    <!--검색 버튼-->
                    <input type='submit' value='검색' onclick='searchOnClick()'>&nbsp;
                    <!--현재 페이지 번호-->
                    <input type='hidden' name='nowPageNum' id='nowPageNum'>
                    <!--화면에 출력할 글 수-->
                    <input type='hidden' name='prtContentsNum' id='prtContentsNum'>
                </form>
            </td>
            <td>
                <form method='post' action='writePage.php' id='goWritePageForm'>
                     <input type='button' value='글쓰기' onclick='writeLoginCheck()'>
                </form>
           </td>
           <td>
               <form method='post' action='loginPage.php' id='goLoginPageForm'>
                      <input type='button' value='로그인' onclick='loginCheck()'>
               </form>
           </td>
           <td>
                <form method='post' action='loginDB.php' id='goLogOut'>
                       <input type='button' value='로그아웃' onclick='logOutStart()'>
                       <input type='hidden' value='1' name='logOut'>
                </form>
           </td>
        </tr>
    </table>";

    echo "<table border = '1' id='listTable'>";
    echo "<tr id='titleName'>";
    echo "<td id='boardNumTd'>글 번호</td>";
    echo "<td id='titleTd'>제목</td>";
    echo "<td id='idTd'>아이디</td>";
    echo "<td id='gradeTd'>등급</td>";
    echo "<td id='hitsTd'>조회수</td>";
    echo "<td id='dateTd'>등록 날짜</td>";
    echo "</tr>";

    for($iCount = 0; $iCount < $prtContentsNum; $iCount++){
        $boardSelectResult = mysqli_fetch_array($boardSelectQuery);

        // 페이지 출력 글 수가 모자를 경우에는 출력하지 않기 위해 if문에 조건을 설정합니다.
        if($boardSelectResult){
            echo "<tr>";
            // 글번호
            echo "<td>$boardSelectResult[0]</td>";
            // 제목
            echo "<td class='titleTdStyle' onmouseover='titleOnMouse(event)' 
                    onmouseout='titleOutMouse(event)' onclick='readWrite(event)'
                    id='$boardSelectResult[0]_board'>$boardSelectResult[3]</td>";
            // 아이디
            echo "<td>$boardSelectResult[1]</td>";
            // 등급
            echo "<td>$boardSelectResult[2]</td>";
            // 조회수
            echo "<td>$boardSelectResult[4]</td>";
            // 등록 날짜
            echo "<td>$boardSelectResult[5]</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    // 페이지 하단바의 개수를 구하는 연산에 사용 될 게시판 글의 수를 구하는 SQL문
    // 검색 내용 변수가 빈값이 아닌 경우, 즉 검색을 하는 경우 조건에 따른
    // $searchWHERE 조건을 사용하여 SQL문을 구성합니다.
    if($searchStr != ""){
        $selectDataSQL = "SELECT board_id FROM $tableName $searchWHERE";
    } else {
        // 게시판의 모든 글 수를 구하는 SQL문
        $selectDataSQL = "SELECT board_id FROM $tableName WHERE board_pid = 0";
    }

    // 게시판의 모든 글 수를 구하는 SQL문을 실행합니다.
    $selectAllDataQuery = mysqli_query($connect, $selectDataSQL);

    // 페이지 수를 구합니다.
    $totalPageNum = mysqli_num_rows($selectAllDataQuery) / $prtContentsNum;

    // 전체 테이블 나누기 한페이지 출력 글 수를 연산하여 나머지가 있을 경우
    // $totalPageNum의 값을 1증가 시킵니다.
    if(mysqli_num_rows($selectAllDataQuery) % $prtContentsNum){
        // 자료형을 int형으로 변환하여 소수점 이하값을 제거합니다.
        settype($totalPageNum,"integer");
        // $totalPageNum의 값을 1증가 시킵니다.
        $totalPageNum++;
    }

    // 출력할 페이지 하단바의 개수를 나타내는 변수의 값을 10보다 작을 경우 하단바의 개수로
    // 10보다 큰 경우 10으로 초기화합니다.
    if($totalPageNum < 10)
        $pageBarPrtNum = $totalPageNum;
    else
        $pageBarPrtNum = 10;

    // 페이지네이션
    echo "<br><div id='pageBar'><b id='goFirstPage' onclick='firstAndLastPageMove(event)'>◀</b>";

    for($iCount = 1; $iCount <= $pageBarPrtNum; $iCount++){
        echo "<b id='$iCount' onclick='pageBarMove(event)'>&nbsp;$iCount&nbsp;</b>";
    }

    echo "<b id='goLastPage' onclick='firstAndLastPageMove(event)'>▶</b></div>";
    echo "</div>";
}

?>
<script language="JavaScript">
    // 로그인 상태일때 글쓰기 페이지로 이동하는 작업을 수행합니다.
    function writeLoginCheck() {
        // 로그인 상태일때 if문을 실행합니다.
        if(<?=$loginValue?> != 0){
            document.getElementById("goWritePageForm").submit();
        } else{
            alert("로그인 해주세요!!\nへ(￣∇￣へ)");
        }
    }
    /********************************************* writeLoginCheck() 함수 끝 *********************************************/

    // 로그인 상태가 아닐 때 로그인 페이지로 이동하는 작업을 수행합니다.
    function loginCheck() {
        // 로그인 상태일때 if문을 실행합니다.
        if(<?=$loginValue?> != 0){
            alert("이미 로그인 하였습니다!!\nへ(￣∇￣へ)");
        } else{
            document.getElementById("goLoginPageForm").submit();
        }
    }
    /********************************************* loginCheck() 함수 끝 *********************************************/

    // 로그아웃을 실행하는 함수
    function logOutStart() {
        // 로그인 상태일 경우
        if(<?=$loginValue?> != 0){
            document.getElementById("goLogOut").submit();
        }
        // 로그아웃 상태일 경우
        else {
            alert("로그인 상태가 아님니다!!\nへ(￣∇￣へ)");
        }
    }
    /********************************************* logOutStart() 함수 끝 *********************************************/

    // 페이지 이동 함수
    function pageBarMove(event) {
        // 페이지 번호
        document.getElementById("nowPageNum").value = event.target.id;

        // 화면에 출력할 글의 수
        document.getElementById("prtContentsNum").value = <?=$prtContentsNum?>;

        // 검색 내용, 검색 내용이 빈값이 아닐 경우 검색을 하였다는 것이므로
        // if문을 실행합니다.
        if(<?=$searchStrJSON?> != ""){
            document.getElementById("searchStr").value = <?=$searchStrJSON?>;
        }

        // listPage.php로 페이지 이동
        document.getElementById("rePage").submit();
    }
    /********************************************* pageBarMove(event) 함수 끝 *********************************************/

    // 첫페이지 또는 마지막 페이지로 이동하는 함수
    function firstAndLastPageMove(event) {
        // 화면에 출력할 글의 수
        document.getElementById("prtContentsNum").value = <?=$prtContentsNum?>;

        if(event.target.id == "goFirstPage"){
            // 페이지 번호에 첫번쨰 페이지 값 1을 대입합니다.
            document.getElementById("nowPageNum").value = 1;
        } else{
            // 페이지 번호
            document.getElementById("nowPageNum").value = <?=$totalPageNum?>;
        }

        // 검색 내용, 검색 내용이 빈값이 아닐 경우 검색을 하였다는 것이므로
        // if문을 실행합니다.
        if(<?=$searchStrJSON?> != ""){
            document.getElementById("searchStr").value = <?=$searchStrJSON?>;
        }

        // listPage.php로 페이지 이동
        document.getElementById("rePage").submit();
    }
    /********************************************* firstAndLastPageMove(event) 함수 끝 *********************************************/

    function searchOnClick() {
        // 화면에 출력할 글의 수
        document.getElementById("prtContentsNum").value = <?=$prtContentsNum?>;

        // 페이지 번호, 검색을 한 결과의 첫번째 페이지 부터 보여주기 위해 1로 초기화 합니다.
        document.getElementById("nowPageNum").value = 1;
    }
    /********************************************* searchOnClick() 함수 끝 *********************************************/

    // 테이블에서 제목을 나타내는 항목 위에 마우스를 올리면 실행되는 함수
    function titleOnMouse(event) {
        event.target.style.color = "red";
    }
    /********************************************* titleOnMouse(event) 함수 끝 *********************************************/

    // 테이블에서 제목을 나타내는 항목 밖으로 마우스를 옮기면 실행되는 함수
    function titleOutMouse(event) {
        event.target.style.color = "black";
    }
    /********************************************* titleOutMouse(event) 함수 끝 *********************************************/

    // 테이블에서 제목을 나타내는 항목을 클릭하면 해당 글의 내용을
    // 볼 수 있는 페이지로 데이터를 전송하며 이동하는 함수
    function readWrite(event) {
        // 테이블 상의 클릭한 제목의 id를 저장합니다.
        var writeID = event.target.id;
        // writeID의 값은 숫자_board 형식으로 되어있는데 _를 기준으로 구분하여 배열에 저장합니다.
        writeID = writeID.split("_");
        // 배열에 저장된 첫번째 값을 boardID을 id로 가지는 hidden 엘리먼트의 값으로 저장하여
        // submit를 할때 글 번호로 사용할 수 있도록 저장합니다.
        document.getElementById("boardID").value = writeID[0];
        // updateForm을 id로 가지는 form에 저장되어 있는 주소 updateAndDeletePage.php로 이동합니다.
        document.getElementById("updateForm").submit();
    }
    /********************************************* readWrite(event) 함수 끝 *********************************************/

    // 검색조건 값이 전달 될 수 있도록 하는 함수
    // 페이지가 로딩되면 바로 이 함수가 실행되도록 합니다.
    window.onload = function selectSet() {
        // searchOptionValue을 name으로 가지는 option속성들을 nodeList형태로 가져옵니다.
        var searchOptionValue = document.getElementsByName("searchOptionValue");

        // searchOptionValue의 길이만큼 반복하여 차레로 이전에 설정하여 전달된
        // 값과 비교하여 같으면 selected 속성에 true, 다르면 false를 대입합니다.
        for(var iCount = 0; iCount < searchOptionValue.length; iCount++){
            if(searchOptionValue[iCount].index == <?=$searchCheck?>)
                searchOptionValue[iCount].selected = true;
            else
                searchOptionValue[iCount].selected = false;
        }
    }
    /********************************************* selectSet() 함수 끝 *********************************************/
</script>
</body>
</html>