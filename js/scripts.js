var attempt = 3;
     //Variable to count the numebr of attempts.
    function validate(){
        var username = document.getElementById("adminusername").value;
        var password = document.getElementById("adminpassword").value;
        if (username == "cybillAdmin" && password == "cybill3241") {
            alert ("Login successfully");
            window.location = "index2.html";
            return false;
        }
        else{
            attempt --; //Decrementing by one. 
            alert("You have left "+attempt+" attempt;");
            //Disabling fields after 3 attempts.
            if( attempt == 0){
                document.getElementById("adminusername").disabled = true;
                document.getElementById("adminpassword").disabled = true;
                document.getElementById("submit").disabled = true;
                return false;
            }
        }
    }
function signin(){
    var user= document.getElementsByName("username");
    var paswd = document.getElementsByName("password");
    

}

$(document).ready(function() {
    $('.menu-toggle').on('click',function() {
        $('.nav').toggleClass('showing');
        $('.nav ul').toggleClass('showing');
    });

});
$('.post-wrapper').slick({
    slidesToShow: 3,
    slidesToScroll:1,
    autoplay: true,
    autoplaySpeed: 2000,
    nextArrow: $('.next'),
    prevArrow: $('.prev'),
    responsive: [
        {
            treakpoint: 1024,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 3,
                infinite: true,
                dots: true
            }
        },
        {
            breakpoint: 600,
            settings: {
                slidesToShow: 2,
                slidesToScroll: 2
            }
        },
        {
            breakpoint: 480,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }
    ]
});

// ClassicEditor
//     .create( document.querySelector( '#editor' ), {
//         toolbar: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote' ],
//         heading: {
//             options: [
//                 { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
//                 { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
//                 { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
//             ]
//         }
//     } )
//     .catch( error => {
//         console.log( error );
//     } );


    
