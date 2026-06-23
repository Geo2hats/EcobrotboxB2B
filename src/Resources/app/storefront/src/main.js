document.addEventListener('DOMContentLoaded', function () {
    let typeBox = document.getElementById('accountType');
    if(typeBox){
        console.log(typeBox);
        console.log("working it");
        typeBox.parentElement?.setAttribute('style', 'display: none !important;');
        typeBox.parentNode?.setAttribute('style', 'display: none !important;');
        typeBox.dispatchEvent(new Event('change'));
    }

    }, false);



let typeBox = document.getElementById('accountType');
if(typeBox){
    console.log(typeBox);
    console.log("working it");
    typeBox.parentElement?.setAttribute('style', 'display: none !important;');
    typeBox.parentNode?.setAttribute('style', 'display: none !important;');
    typeBox.dispatchEvent(new Event('change'));
}
