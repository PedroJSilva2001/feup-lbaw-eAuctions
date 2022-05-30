/* Create Auction */

// Image upload
function addEventListeners() {
    const inputFile = document.querySelector('input[type="file"]');
    if (inputFile) {
        inputFile.addEventListener('change', (function(e) {
            document.querySelector("#preview").innerHTML = '';
        
            for (file of e.target.files){
                
                var reader = new FileReader();
                reader.addEventListener('load', (e) => {
                    document.querySelector("#preview").innerHTML += `<img src="`+  e.target.result +`" class="img-thumbnail col-sm-2">`;
                });
                reader.readAsDataURL(file);
            }
        }));
    }
}
  
function commentsScrollDown() {
    const display_comments_div = document.querySelector('.display-comment');
    if (display_comments_div) {
        display_comments_div.scrollTop = display_comments_div.scrollHeight;
    }
}

addEventListeners();
commentsScrollDown();