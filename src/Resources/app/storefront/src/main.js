document.addEventListener('DOMContentLoaded', function () {
    let typeBox = document.getElementById('accountType');
    console.log(typeBox);
    console.log("working it");
    typeBox.parentElement.setAttribute('style', 'display: none !important;');
    typeBox.parentNode.setAttribute('style', 'display: none !important;');
    typeBox.dispatchEvent(new Event('change'));
    }, false);


    console.log("working it");

    let typeBox = document.getElementById('accountType');
    console.log(typeBox);
    console.log("working it");
    typeBox.parentElement.setAttribute('style', 'display: none !important;');
    typeBox.parentNode.setAttribute('style', 'display: none !important;');
    typeBox.dispatchEvent(new Event('change'));