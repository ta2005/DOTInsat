const studyYear = document.querySelector("#StudyYear");
const studyField = document.querySelector("#StudyField");

const updateSelection = () => {
    const year = studyYear.value;
    
    switch (year) {
        case "1":
            studyField.innerHTML = `
                <option value="none">Select Field</option>
                <option value="MPI">MPI</option>
            `; 
            break;
            
        case "2":
        case "3":
        case "4":
            studyField.innerHTML = `
                <option value="none">Select Field</option>
                <option value="GL">GL</option>
                <option value="RT">RT</option>
                <option value="IMI">IMI</option>
                <option value="IIA">IIA</option>
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
        }
    } else if (year === "2") {
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
        }
    } else if (year === "3") {
        let pageLink = document.createElement("a");
        switch (field) {
            case "GL":
                pageLink.setAttribute("href", "gl3web.php");
                pageLink.click();
                break;
            case "RT":
                pageLink.setAttribute("href", "rt3web.php"); 
                pageLink.click();
                break;
            case "IMI":
                pageLink.setAttribute("href", "imi3web.php"); 
                pageLink.click();
                break;
            case "IIA":
                pageLink.setAttribute("href", "iia3web.php"); 
                pageLink.click();
                break;
            
        }
    } else if (year === "4") {
        let pageLink = document.createElement("a");
        switch (field) {
            case "GL":
                pageLink.setAttribute("href", "gl4web.php");
                pageLink.click();
                break;
            case "RT":
                pageLink.setAttribute("href", "rt4web.php"); 
                pageLink.click();
                break;
            case "IMI":
                pageLink.setAttribute("href", "imi4web.php"); 
                pageLink.click();
                break;
            case "IIA":
                pageLink.setAttribute("href", "iia4web.php"); 
                pageLink.click();
                break;
        }
    }
}
