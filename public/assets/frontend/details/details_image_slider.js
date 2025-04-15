// let thumbnails = document.getElementsByClassName('thumbnail')

// 		let activeImages = document.getElementsByClassName('active')

// 		for (var i=0; i < thumbnails.length; i++){

// 			thumbnails[i].addEventListener('mouseover', function(){
// 				console.log(activeImages)
				
// 				if (activeImages.length > 0){
// 					activeImages[0].classList.remove('active')
// 				}
				

// 				this.classList.add('active')
// 				document.getElementById('featured').src = this.src
// 			})
// 		}


// 		let buttonRight = document.getElementById('slideRight');
// 		let buttonLeft = document.getElementById('slideLeft');

// 		buttonLeft.addEventListener('click', function(){
// 			document.getElementById('slider').scrollLeft -= 180
// 		})

// 		buttonRight.addEventListener('click', function(){
// 			document.getElementById('slider').scrollLeft += 180
// 		})





// document.addEventListener('DOMContentLoaded', function () {
//         // Find the first active thumbnail
//         let activeThumbnail = document.querySelector('.thumbnail.active');
//         if (activeThumbnail) {
//             // Set its source to the featured image
//             document.getElementById('featured').src = activeThumbnail.src;
//         }

//         let thumbnails = document.getElementsByClassName('thumbnail');
//         let activeImages = document.getElementsByClassName('active');

//         for (let i = 0; i < thumbnails.length; i++) {
//             thumbnails[i].addEventListener('mouseover', function () {
//                 if (activeImages.length > 0) {
//                     activeImages[0].classList.remove('active');
//                 }
//                 this.classList.add('active');
//                 document.getElementById('featured').src = this.src;
//             });
//         }

//         let buttonRight = document.getElementById('slideRight');
//         let buttonLeft = document.getElementById('slideLeft');

//         buttonLeft.addEventListener('click', function () {
//             document.getElementById('slider').scrollLeft -= 180;
//         });

//         buttonRight.addEventListener('click', function () {
//             document.getElementById('slider').scrollLeft += 180;
//         });
//     });


document.addEventListener('DOMContentLoaded', function () {
    // Function to update the featured image based on the active thumbnail
    function updateFeaturedImage() {
        let activeThumbnail = document.querySelector('.thumbnail.active');
        if (activeThumbnail) {
            document.getElementById('featured').src = activeThumbnail.src;
        }
    }

    // Set the initial featured image based on the first active thumbnail
    updateFeaturedImage();

    let thumbnails = document.getElementsByClassName('thumbnail');
    let activeImages = document.getElementsByClassName('active');

    // Add mouseover event to thumbnails
    for (let i = 0; i < thumbnails.length; i++) {
        thumbnails[i].addEventListener('mouseover', function () {
            if (activeImages.length > 0) {
                activeImages[0].classList.remove('active');
            }
            this.classList.add('active');
            updateFeaturedImage(); // Update the featured image
        });
    }

    // Observe changes in the class attribute of thumbnails
    let observer = new MutationObserver(function (mutationsList) {
        for (let mutation of mutationsList) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                updateFeaturedImage(); // Update the featured image on class change
            }
        }
    });

    // Add observer to each thumbnail
    for (let thumbnail of thumbnails) {
        observer.observe(thumbnail, { attributes: true });
    }

    // Slider functionality
    let buttonRight = document.getElementById('slideRight');
    let buttonLeft = document.getElementById('slideLeft');

    buttonLeft.addEventListener('click', function () {
        document.getElementById('slider').scrollLeft -= 180;
    });

    buttonRight.addEventListener('click', function () {
        document.getElementById('slider').scrollLeft += 180;
    });
});
