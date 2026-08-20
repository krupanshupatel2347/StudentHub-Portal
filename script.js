/* ==========================================================================
   StudentHub Portal - Client-Side Interactivity
   Subject: Web Development Frameworks (ITUE203)
   Student Name: Krupanshu Patel (25DCS088)
   ========================================================================== */

document.addEventListener("DOMContentLoaded", function () {
    // 1. NOTIFICATION BANNER CLOSE
    const notification = document.getElementById("notification");
    const closeNotification = document.getElementById("closeNotification");
    if (notification && closeNotification) {
        closeNotification.addEventListener("click", function () {
            notification.style.display = "none";
        });
    }

    // 2. MOBILE HAMBURGER MENU
    const menuButton = document.getElementById("menuButton");
    const navbar = document.getElementById("navbar");
    if (menuButton && navbar) {
        menuButton.addEventListener("click", function () {
            navbar.classList.toggle("show-menu");
        });
    }

    // 3. LIGHT / DARK THEME SWITCHER WITH LOCALSTORAGE
    const themeButton = document.getElementById("themeButton");
    const savedTheme = localStorage.getItem("studenthub_theme");
    if (savedTheme === "dark") {
        document.body.classList.add("dark-mode");
        if (themeButton) themeButton.textContent = "☀️ Light Mode";
    }

    if (themeButton) {
        themeButton.addEventListener("click", function () {
            document.body.classList.toggle("dark-mode");
            if (document.body.classList.contains("dark-mode")) {
                themeButton.textContent = "☀️ Light Mode";
                localStorage.setItem("studenthub_theme", "dark");
            } else {
                themeButton.textContent = "🌙 Dark Mode";
                localStorage.setItem("studenthub_theme", "light");
            }
        });
    }

    // 4. COLLAPSIBLE FAQ ACCORDION
    const questions = document.querySelectorAll(".faq-question");
    questions.forEach(function (question) {
        question.addEventListener("click", function () {
            const answer = question.nextElementSibling;
            if (answer.style.display === "block") {
                answer.style.display = "none";
                question.classList.remove("active");
            } else {
                answer.style.display = "block";
                question.classList.add("active");
            }
        });
    });

    // 5. MODAL POPUP (COURSES)
    const modal = document.getElementById("registrationModal");
    const openModal = document.getElementById("openModal");
    const closeModal = document.getElementById("closeModal");
    const goToSignup = document.getElementById("goToSignup");

    if (openModal && modal) {
        openModal.addEventListener("click", function () {
            modal.style.display = "block";
        });
    }

    if (closeModal && modal) {
        closeModal.addEventListener("click", function () {
            modal.style.display = "none";
        });
    }

    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    if (goToSignup) {
        goToSignup.addEventListener("click", function () {
            window.location.href = "Courses.html";
        });
    }

    // 6. IMAGE / CONTENT SLIDER
    const sliderImage = document.getElementById("sliderImage");
    const sliderTitle = document.getElementById("sliderTitle");
    const sliderText = document.getElementById("sliderText");
    const previousSlide = document.getElementById("previousSlide");
    const nextSlide = document.getElementById("nextSlide");

    const slides = [
        {
            image: "Charusat.jpg",
            title: "CHARUSAT",
            text: "Welcome to CHARUSAT campus."
        },
        {
            image: "Campus.jpg",
            title: "Campus",
            text: "Explore the beautiful green university campus."
        },
        {
            image: "Student.jpg",
            title: "Students",
            text: "A vibrant academic environment for aspiring engineers."
        }
    ];

    let currentSlide = 0;

    function showSlide(index) {
        if (!sliderImage) return;
        sliderImage.src = slides[index].image;
        sliderImage.alt = slides[index].title;
        if (sliderTitle) sliderTitle.textContent = slides[index].title;
        if (sliderText) sliderText.textContent = slides[index].text;
    }

    if (sliderImage && previousSlide && nextSlide) {
        showSlide(currentSlide);

        nextSlide.addEventListener("click", function () {
            currentSlide++;
            if (currentSlide >= slides.length) {
                currentSlide = 0;
            }
            showSlide(currentSlide);
        });

        previousSlide.addEventListener("click", function () {
            currentSlide--;
            if (currentSlide < 0) {
                currentSlide = slides.length - 1;
            }
            showSlide(currentSlide);
        });

        setInterval(function () {
            currentSlide++;
            if (currentSlide >= slides.length) {
                currentSlide = 0;
            }
            showSlide(currentSlide);
        }, 4500);
    }
});

// --------------------------------------------------------------------------
// PRACTICAL 5: CLIENT-SIDE REGISTRATION & LOGIN VALIDATION WITH REGEX
// --------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", function () {
    const signupForm = document.getElementById("signupForm");
    if (signupForm) {
        signupForm.addEventListener("submit", function (event) {
            // Regex patterns
            const nameRegex = /^[A-Za-z ]{2,50}$/;
            const emailRegex = /^[\w.-]+@[\w.-]+\.\w{2,}$/;
            const mobileRegex = /^[6-9]\d{9}$/;
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

            // Form inputs
            const name = document.getElementById("fullName").value.trim();
            const email = document.getElementById("signupEmail").value.trim();
            const mobile = document.getElementById("mobile").value.trim();
            const password = document.getElementById("signupPassword").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            const course = document.getElementById("course").value;
            const year = document.getElementById("year").value;
            const gender = document.querySelector('input[name="gender"]:checked');
            const terms = document.getElementById("terms").checked;

            // Clear previous errors
            document.querySelectorAll(".error-message").forEach(function (el) {
                el.textContent = "";
            });

            let valid = true;

            // 1. Name validation
            if (!nameRegex.test(name)) {
                document.getElementById("nameError").textContent = "Enter a valid name (letters and spaces only, min 2 chars).";
                valid = false;
            }

            // 2. Email validation
            if (!emailRegex.test(email)) {
                document.getElementById("emailError").textContent = "Enter a valid email address.";
                valid = false;
            }

            // 3. Mobile validation
            if (!mobileRegex.test(mobile)) {
                document.getElementById("mobileError").textContent = "Enter a valid 10-digit mobile number starting with 6-9.";
                valid = false;
            }

            // 4. Password validation
            if (!passwordRegex.test(password)) {
                document.getElementById("passwordError").textContent = "Password must be 8+ characters with uppercase, lowercase and a number.";
                valid = false;
            }

            // 5. Confirm password
            if (password !== confirmPassword) {
                document.getElementById("confirmPasswordError").textContent = "Passwords do not match.";
                valid = false;
            }

            // 6. Course
            if (course === "") {
                document.getElementById("courseError").textContent = "Please select a course.";
                valid = false;
            }

            // 7. Year
            if (year === "" || Number(year) < 1 || Number(year) > 4) {
                document.getElementById("yearError").textContent = "Enter a valid year from 1 to 4.";
                valid = false;
            }

            // 8. Gender
            if (!gender) {
                document.getElementById("genderError").textContent = "Please select your gender.";
                valid = false;
            }

            // 9. Terms
            if (!terms) {
                document.getElementById("termsError").textContent = "You must accept the Terms & Conditions.";
                valid = false;
            }

            if (!valid) {
                event.preventDefault();
            }
        });
    }

    // LOGIN VALIDATION
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        loginForm.addEventListener("submit", function (event) {
            const emailRegex = /^[\w.-]+@[\w.-]+\.\w{2,}$/;
            const email = document.getElementById("loginEmail").value.trim();
            const password = document.getElementById("loginPassword").value;

            const emailError = document.getElementById("loginEmailError");
            const passError = document.getElementById("loginPasswordError");
            if (emailError) emailError.textContent = "";
            if (passError) passError.textContent = "";

            let valid = true;
            if (!emailRegex.test(email)) {
                if (emailError) emailError.textContent = "Enter a valid email address.";
                valid = false;
            }
            if (password.length < 6) {
                if (passError) passError.textContent = "Password must be at least 6 characters.";
                valid = false;
            }
            if (!valid) {
                event.preventDefault();
            }
        });
    }
});

// --------------------------------------------------------------------------
// PRACTICAL 6: RENDERING EXTERNAL JSON DATA (FAQ & EVENTS) USING FETCH API
// --------------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", function () {
    // A. DYNAMIC FAQS (faqs.json)
    const faqContainer = document.getElementById("faqContainer");
    const searchInput = document.getElementById("searchInput");
    const sortSelect = document.getElementById("sortSelect");
    const faqPagination = document.getElementById("pagination");
    const loadingMessage = document.getElementById("loadingMessage");
    const errorMessage = document.getElementById("errorMessage");

    if (faqContainer && searchInput && sortSelect && faqPagination) {
        let faqData = [];
        let filteredFaqs = [];
        let currentPage = 1;
        const recordsPerPage = 5;

        if (loadingMessage) loadingMessage.style.display = "block";

        fetch("faqs.json")
            .then(res => {
                if (!res.ok) throw new Error("Could not load FAQs");
                return res.json();
            })
            .then(data => {
                if (loadingMessage) loadingMessage.style.display = "none";
                faqData = data;
                filteredFaqs = [...faqData];
                displayFAQs();
            })
            .catch(err => {
                if (loadingMessage) loadingMessage.style.display = "none";
                if (errorMessage) {
                    errorMessage.textContent = "Error loading FAQs. Please check network.";
                    errorMessage.style.display = "block";
                }
            });

        function displayFAQs() {
            faqContainer.innerHTML = "";
            const startIndex = (currentPage - 1) * recordsPerPage;
            const endIndex = startIndex + recordsPerPage;
            const currentFaqs = filteredFaqs.slice(startIndex, endIndex);

            if (currentFaqs.length === 0) {
                faqContainer.innerHTML = "<p style='text-align: center; padding: 20px;'>No FAQs found matching your query.</p>";
                faqPagination.innerHTML = "";
                return;
            }

            currentFaqs.forEach(faq => {
                const item = document.createElement("div");
                item.className = "faq-item";
                item.innerHTML = `
                    <button class="faq-question">
                        ${faq.id}. ${faq.question}
                        <span>+</span>
                    </button>
                    <div class="faq-answer">
                        <p>${faq.answer}</p>
                    </div>
                `;
                faqContainer.appendChild(item);

                const qBtn = item.querySelector(".faq-question");
                qBtn.addEventListener("click", function () {
                    const ans = item.querySelector(".faq-answer");
                    if (ans.style.display === "block") {
                        ans.style.display = "none";
                        qBtn.classList.remove("active");
                    } else {
                        ans.style.display = "block";
                        qBtn.classList.add("active");
                    }
                });
            });

            renderFaqPagination();
        }

        function renderFaqPagination() {
            faqPagination.innerHTML = "";
            const totalPages = Math.ceil(filteredFaqs.length / recordsPerPage);
            if (totalPages <= 1) return;

            const prevBtn = document.createElement("button");
            prevBtn.textContent = "Previous";
            prevBtn.disabled = currentPage === 1;
            prevBtn.addEventListener("click", () => {
                currentPage--;
                displayFAQs();
            });
            faqPagination.appendChild(prevBtn);

            for (let p = 1; p <= totalPages; p++) {
                const btn = document.createElement("button");
                btn.textContent = p;
                if (p === currentPage) btn.classList.add("active-page");
                btn.addEventListener("click", () => {
                    currentPage = p;
                    displayFAQs();
                });
                faqPagination.appendChild(btn);
            }

            const nextBtn = document.createElement("button");
            nextBtn.textContent = "Next";
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.addEventListener("click", () => {
                currentPage++;
                displayFAQs();
            });
            faqPagination.appendChild(nextBtn);
        }

        searchInput.addEventListener("input", function () {
            const query = searchInput.value.toLowerCase().trim();
            filteredFaqs = faqData.filter(faq =>
                faq.question.toLowerCase().includes(query) ||
                faq.answer.toLowerCase().includes(query)
            );
            currentPage = 1;
            applyFaqSort();
            displayFAQs();
        });

        function applyFaqSort() {
            const val = sortSelect.value;
            if (val === "az") {
                filteredFaqs.sort((a, b) => a.question.localeCompare(b.question));
            } else if (val === "za") {
                filteredFaqs.sort((a, b) => b.question.localeCompare(a.question));
            }
        }

        sortSelect.addEventListener("change", function () {
            applyFaqSort();
            currentPage = 1;
            displayFAQs();
        });
    }

    // B. DYNAMIC EVENTS (events.json)
    const eventContainer = document.getElementById("eventsContainer");
    const eventSearch = document.getElementById("eventSearch");
    const eventCategory = document.getElementById("eventCategory");
    const eventSort = document.getElementById("eventSort");
    const eventPagination = document.getElementById("eventPagination");
    const eventLoading = document.getElementById("eventLoading");
    const eventError = document.getElementById("eventError");

    if (eventContainer && eventSearch && eventCategory && eventSort && eventPagination) {
        let eventData = [];
        let filteredEvents = [];
        let currentEventPage = 1;
        const eventsPerPage = 4;

        if (eventLoading) eventLoading.style.display = "block";

        fetch("events.json")
            .then(res => {
                if (!res.ok) throw new Error("Could not load events data");
                return res.json();
            })
            .then(data => {
                if (eventLoading) eventLoading.style.display = "none";
                eventData = data;
                filteredEvents = [...eventData];
                displayEvents();
            })
            .catch(err => {
                if (eventLoading) eventLoading.style.display = "none";
                if (eventError) {
                    eventError.textContent = "Error loading events. Please try again.";
                    eventError.style.display = "block";
                }
            });

        function displayEvents() {
            eventContainer.innerHTML = "";
            const startIndex = (currentEventPage - 1) * eventsPerPage;
            const endIndex = startIndex + eventsPerPage;
            const paged = filteredEvents.slice(startIndex, endIndex);

            if (paged.length === 0) {
                eventContainer.innerHTML = "<p style='text-align:center; grid-column: 1/-1;'>No events found.</p>";
                eventPagination.innerHTML = "";
                return;
            }

            paged.forEach(ev => {
                const card = document.createElement("div");
                card.className = "event-card";
                card.innerHTML = `
                    <h2>${ev.name}</h2>
                    <p><strong>Category:</strong> ${ev.category}</p>
                    <p><strong>Date & Time:</strong> ${ev.date} at ${ev.time}</p>
                    <p><strong>Venue:</strong> ${ev.venue}</p>
                    <p>${ev.description}</p>
                `;
                eventContainer.appendChild(card);
            });

            renderEventPagination();
        }

        function renderEventPagination() {
            eventPagination.innerHTML = "";
            const total = Math.ceil(filteredEvents.length / eventsPerPage);
            if (total <= 1) return;

            const prevBtn = document.createElement("button");
            prevBtn.textContent = "Previous";
            prevBtn.disabled = currentEventPage === 1;
            prevBtn.addEventListener("click", () => {
                currentEventPage--;
                displayEvents();
            });
            eventPagination.appendChild(prevBtn);

            for (let i = 1; i <= total; i++) {
                const btn = document.createElement("button");
                btn.textContent = i;
                if (i === currentEventPage) btn.classList.add("active-page");
                btn.addEventListener("click", () => {
                    currentEventPage = i;
                    displayEvents();
                });
                eventPagination.appendChild(btn);
            }

            const nextBtn = document.createElement("button");
            nextBtn.textContent = "Next";
            nextBtn.disabled = currentEventPage === total;
            nextBtn.addEventListener("click", () => {
                currentEventPage++;
                displayEvents();
            });
            eventPagination.appendChild(nextBtn);
        }

        function filterAndSortEvents() {
            const query = eventSearch.value.toLowerCase().trim();
            const cat = eventCategory.value;

            filteredEvents = eventData.filter(ev => {
                const matchesSearch = ev.name.toLowerCase().includes(query) ||
                                      ev.description.toLowerCase().includes(query) ||
                                      ev.venue.toLowerCase().includes(query);
                const matchesCat = (cat === "all") || (ev.category === cat);
                return matchesSearch && matchesCat;
            });

            const sortVal = eventSort.value;
            if (sortVal === "nameAsc") {
                filteredEvents.sort((a, b) => a.name.localeCompare(b.name));
            } else if (sortVal === "nameDesc") {
                filteredEvents.sort((a, b) => b.name.localeCompare(a.name));
            } else if (sortVal === "dateAsc") {
                filteredEvents.sort((a, b) => new Date(a.date) - new Date(b.date));
            } else if (sortVal === "dateDesc") {
                filteredEvents.sort((a, b) => new Date(b.date) - new Date(a.date));
            }

            currentEventPage = 1;
            displayEvents();
        }

        eventSearch.addEventListener("input", filterAndSortEvents);
        eventCategory.addEventListener("change", filterAndSortEvents);
        eventSort.addEventListener("change", filterAndSortEvents);
    }
});
