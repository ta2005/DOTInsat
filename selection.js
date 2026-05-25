const studyYear = document.querySelector("#StudyYear");
const studyField = document.querySelector("#StudyField");

const updateSelection = () => {
    const year = studyYear.value;
    
    switch (year) {
        case "1":
            studyField.innerHTML = `
                <option value="none">Select Field</option>
                <option value="MPI">MPI</option>
                <option value="CBA">CBA</option>
            `; 
            break;
            
        case "2":
        case "3":
        case "4":
        case "5":
            studyField.innerHTML = `
                <option value="none">Select Field</option>
                <option value="GL">GL</option>
                <option value="RT">RT</option>
                <option value="IMI">IMI</option>
                <option value="IIA">IIA</option>
                <option value="CH">CH</option>
                <option value="BIO">BIO</option>
            `;
            break;
            
        default:
            studyField.innerHTML = `
                <option value="none">Select Year First</option>
            `;
            break;
    }
}

studyYear.addEventListener("change", updateSelection);

const load = () => {
    const year = studyYear.value;
    const field = studyField.value;
    
    if (year === "none" || field === "none") {
        alert("Select Year and Field Please :)");
        return;
    }
 
    if (year === "1") {
        if (field === "MPI") {
            let pageLink = document.createElement("a");
            pageLink.setAttribute("href", "mpiweb.php"); 
            pageLink.click();
        } else if (field === "CBA") {
            let pageLink = document.createElement("a");
            pageLink.setAttribute("href", "cbaweb.php"); 
            pageLink.click();
        }
    } else {
        let pageLink = document.createElement("a");
        switch (field) {
            case "GL":
                pageLink.setAttribute("href", "gl2web.php");
                pageLink.click();
                break;
            case "RT":
                pageLink.setAttribute("href", "rt2web.php"); 
                pageLink.click();
                break;
            case "IMI":
                pageLink.setAttribute("href", "imi2web.php"); 
                pageLink.click();
                break;
            case "IIA":
                pageLink.setAttribute("href", "iia2web.php"); 
                pageLink.click();
                break;
            case "CH":
                pageLink.setAttribute("href", "ch2web.php"); 
                pageLink.click();
                break;
            case "BIO":
                pageLink.setAttribute("href", "bio2web.php"); 
                pageLink.click();
                break;
        }
    }
}