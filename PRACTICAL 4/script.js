let menuBtn = document.getElementById("menuBtn");
let navMenu = document.getElementById("navMenu");

if (menuBtn) {
    menuBtn.addEventListener("click", function() {
        navMenu.classList.toggle("show");
    });
}
let closeNotification =
    document.getElementById("closeNotification");
let notification =
    document.getElementById("notification");
if (closeNotification) {
    closeNotification.addEventListener("click", function() {
        notification.style.display = "none";
    });
}
let openModal =
    document.getElementById("openModal");
let closeModal =
    document.getElementById("closeModal");
let modal =
    document.getElementById("modal");
if (openModal) {
    openModal.addEventListener("click", function() {
        modal.style.display = "block";
    });
}
if (closeModal) {
    closeModal.addEventListener("click", function() {
        modal.style.display = "none";
    });
}
let themeBtn =
    document.getElementById("themeBtn");
if (themeBtn) {
    // Check saved theme
    let savedTheme = localStorage.getItem("theme");
    if (savedTheme == "dark") {
        document.body.classList.add("dark");
        themeBtn.innerHTML = "☀️ Light Mode";
    }
 // Change theme when button is clicked
    themeBtn.addEventListener("click", function() {
        document.body.classList.toggle("dark");
     if (document.body.classList.contains("dark")) {
            localStorage.setItem("theme", "dark");
            themeBtn.innerHTML = "☀️ Light Mode";
        }
        else {
            localStorage.setItem("theme", "light");
            themeBtn.innerHTML = "🌙 Dark Mode";
        }
    });
}
// faq mata
let questions =
    document.querySelectorAll(".faq-question");
questions.forEach(function(question) {
    question.addEventListener("click", function() {
        let answer = question.nextElementSibling;
        if (answer.style.display == "block") {
            answer.style.display = "none";
        } else {
            answer.style.display = "block";
        }
    });
});
let events = [
    {
        image: "techfest.png",
        title: "Tech Fest",
        description: "Join the annual technology festival and participate in exciting activities."
    },
    {
        image: "sports event.png",
        title: "Sports Day",
        description: "Enjoy different sports activities and participate with your friends."
    },
    {
        image: "culturalfest.png",
        title: "Cultural Fest",
        description: "Enjoy cultural performances, music, dance and other activities."
    }
];
let currentSlide = 0;
let sliderImage =
    document.getElementById("sliderImage");
let sliderTitle =
    document.getElementById("sliderTitle");
let sliderDescription =
    document.getElementById("sliderDescription");
let nextBtn =
    document.getElementById("nextBtn");
let previousBtn =
    document.getElementById("previousBtn");
function showSlide() {
    sliderImage.src =
        events[currentSlide].image;
    sliderTitle.innerHTML =
        events[currentSlide].title;
    sliderDescription.innerHTML =
        events[currentSlide].description;
}
if (nextBtn) {
    nextBtn.addEventListener("click", function() {
        currentSlide++;
        if (currentSlide >= events.length) {
            currentSlide = 0;
        }
        showSlide();
    });
}
if (previousBtn) {
    previousBtn.addEventListener("click", function() {
        currentSlide--;
        if (currentSlide < 0) {
            currentSlide = events.length - 1;
        }
        showSlide();
    });
}