var i = 2;
function addUserInputAndButton(event) {
    // Prevent the button click from submitting the form
    event.preventDefault();
    i = i + 1
    // Get current inputs
    let inputs = document.querySelectorAll("input.user");

    // Create remove user button (-)
    let button = document.createElement("button");
    button.setAttribute("id", "RemoveUser" + i);
    button.classList.add("user");
    let selector = "#SwSDiv" + i;
    button.addEventListener("click", function (event) {
        event.preventDefault();
        let element = document.querySelector(selector);
        element.remove();
    });
    button.innerText = "-";

    // Create ul
    let ul = document.createElement("ul");
    ul.setAttribute("id", "userList" + i);
    ul.classList.add("user");

    // Create add user input
    let input = document.createElement("input");
    input.setAttribute("name", i);
    input.setAttribute("type", "search");
    input.classList.add("user");

    // Create div container
    let div = document.createElement("div");
    div.setAttribute("id", "SwSDiv" + i);
    div.classList.add("sweetSpot");

    // Append the input and button to the div
    div.append(input);
    div.append(button);
    div.append(ul);

    // Append the div to the users section
    let sweetSpotSection = document.querySelector("section#users");
    sweetSpotSection.appendChild(div);
}

document
    .querySelector("button#addUser")
    .addEventListener("click", addUserInputAndButton)
    ;

fetch("endpoints/getNumberOfPlayerSubmissions.php")
    .then((response) => response.json())
    .then((data) => {
        console.log(data.numberOfSubmissions);
    });
