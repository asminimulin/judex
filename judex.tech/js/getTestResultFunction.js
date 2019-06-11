function getTestResult(sub_id, testNum){
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "scripts/getTestResult.php", true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.onload = ()=>{
        var ans = JSON.parse(xhr.responseText);
        var tmpElem = document.getElementById("winTestResult");
        tmpElem.lastElementChild.firstElementChild.innerText = "Тест #"+(testNum+1);
        tmpElem.removeAttribute("style");
        var workElem = tmpElem.lastElementChild.getElementsByClassName("content")[0].getElementsByTagName("table")[0].getElementsByTagName("tr")[1];
        for (let i = 0; i < 3; ++i) {
            if (workElem.firstChild == undefined || workElem.firstChild == null) {
                break;
            } else {
                workElem.removeChild(workElem.firstChild);
            }
        }
        document.getElementById("form_sub_id").value = sub_id;
        document.getElementById("form_test_num").value = testNum;
        var tmpElem1 = document.createElement("td");
        tmpElem1.innerText = ans['in'] || "";
        tmpElem.style = "overflow:auto;";
        workElem.appendChild(tmpElem1);
        var tmpElem2 = document.createElement("td");
        tmpElem2.innerText = ans['right'] || "";
        tmpElem2.style = "overflow:auto;";
        workElem.appendChild(tmpElem2);
        var tmpElem3 = document.createElement("td");
        tmpElem3.innerText = ans['real'] || "";
        tmpElem3.style = "overflow:auto;";
        workElem.appendChild(tmpElem3);
    }
    xhr.onerror = xhr.onabort = ()=>{
        alert("Ошибка: GETTESTRESULTFUNCTIONERROR");
    }
    xhr.send("test_number="+testNum+"&submission_id="+sub_id);
}