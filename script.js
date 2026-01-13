document.addEventListener('DOMContentLoaded', () => {
    // Hamburger Menu
    const hamburgerMenu = document.querySelector(".hamburger-menu");
    const navMenu = document.querySelector("nav ul");

    if(hamburgerMenu) {
        hamburgerMenu.addEventListener("click", () => {
        hamburgerMenu.classList.toggle("active");
        navMenu.classList.toggle("show");
    });
    }
    //----------------------------------------------
    // Slider Function
    const track = document.querySelector('.cabins'); // Wrap the whole cabins for sliding function
    const cabins = document.querySelectorAll('.cabin'); // Get total of cabins
    const slideLeft = document.querySelector('.arrow-left');
    const slideRight = document.querySelector('.arrow-right');
    const totalBlocks = cabins.length;
    if (cabins.length > 0) {
        const firstCabin = cabins[0];
        const styles = window.getComputedStyle(firstCabin);
        const marginLeft = parseInt(styles.marginLeft, 10); // Convert 10px to 10;
        const marginRight = parseInt(styles.marginRight, 10); // Convert 10px to 10;
        let currentIndex = 0; // start at first cabin

        function moveSlider() {
            let blockWidth;
            let maxIndex;
            const cabin = cabins[0];
            if (!cabin) return;
            if (window.innerWidth > 1600) {
                blockWidth = document.querySelector(".cabin").offsetWidth + marginLeft + marginRight;
                maxIndex = totalBlocks - 3;
            } else {
                blockWidth = document.querySelector(".cabin").offsetWidth;
                maxIndex = totalBlocks - 1;
            }
            if (currentIndex > maxIndex) {
                currentIndex = 0;
            }
            if (currentIndex < 0) {
                currentIndex = maxIndex
            };
            track.style.transition = 'transform 0.5s ease';
            track.style.transform = `translateX(-${currentIndex * blockWidth}px)`;
        }
        // Right Arrow
        slideRight.addEventListener('click', () => {
            currentIndex++;
            moveSlider();
        });
        // Left Arrow
        slideLeft.addEventListener('click', () => {
            currentIndex--;
            moveSlider();
        });
    }
});
// -------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    // Cabin selection display
    const cabinSelect = document.getElementById("cabin-selection");
    const cabins = document.querySelectorAll(".cabin-display");
    const addNewCabin = document.querySelector(".add-new-cabin");
    const addNewCabinButton = document.querySelector(".add-cabin");
    if (cabinSelect) {
        cabinSelect.addEventListener("change", () => {
            const cabinSelectId = cabinSelect.value;
            cabins.forEach(cabin => {
                if (cabin.dataset.id === cabinSelectId) {
                    cabin.style.display = "flex";
                    addNewCabin.style.display = "none";
                    addNewCabinButton.style.display = "none";
                    // Allow cabin message not display when select another cabin
                    const cabinMessage = cabin.querySelector(".message");
                    if (cabinMessage) {
                        cabinMessage.style.display = "none";
                    }
                } else {
                    cabin.style.display = "none";
                }
            });
        });
        // Add new cabin
        const addCabinBtn = document.querySelector(".add-cabin");
        if (addCabinBtn) {
            addCabinBtn.addEventListener("click", () => {
                // Add new cabin to select dropdown
                const option = document.createElement("option");
                option.value = newCabinDiv.dataset.id;
                cabinSelect.appendChild(option);
                cabinSelect.value = newCabinDiv.dataset.id;
            });
        }
    }
});
// -------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    // User selection display
    const userSelect = document.getElementById("user-selection");
    const users = document.querySelectorAll(".user-display");
    const addNewUser = document.querySelector(".new-user");
    const addNewUserButton = document.querySelector(".add-user-button");
    if (!userSelect) return;
    if(userSelect) {
        userSelect.addEventListener("change", () => {
            const userSelectId = userSelect.value;
            users.forEach(user => {
                if (user.dataset.id === userSelectId) {
                    user.style.display = "flex";
                    if (addNewUser) addNewUser.style.display = "none";
                    if (addNewUserButton) addNewUserButton.style.display = "none";
                } else {
                    user.style.display = "none";
                }
            const userMessages = user.querySelectorAll(".message");
            userMessages.forEach(userMessage => userMessage.style.display = "none");
        });
    });
    }
    
    // Add new user
    if (addNewUserButton) {
        const addUserButton = document.querySelector(".add-user-button");
        addUserButton.addEventListener("click", () => {
            if (!addNewUser) return;
            // Add new user to select dropdown
            const option = document.createElement("option");
            option.value = newUserDiv.dataset.id;
            userSelect.appendChild(option);
            userSelect.value = newUserDiv.dataset.id;
        });
    }
});
// -------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    // Function to handle price adjustment
    function priceAdjust(input, adjust) {
        if (!input || !adjust) return;

        adjust.addEventListener('change', () => {
            const adjustNumber = parseFloat(adjust.value);
            // store original prices
            const original = parseFloat(input.dataset.original || input.value);
            input.dataset.original = original;
            input.value = (original * adjustNumber).toFixed(2);
        });
    }

    // New Cabin
    const pricePerNight = document.querySelector('.add-new-cabin .pricePerNight');
    const newAdjustNight = document.querySelector('.add-new-cabin .newAdjustNight');
    const pricePerWeek = document.querySelector('.add-new-cabin .pricePerWeek');
    const newAdjustWeek = document.querySelector('.add-new-cabin .newAdjustWeek');

    priceAdjust(pricePerNight, newAdjustNight);
    priceAdjust(pricePerWeek, newAdjustWeek);

    // Existing Cabins
    const cabins = document.querySelectorAll('.cabin-display');
    if(cabins) {
        cabins.forEach(cabin => {
            const oldPricePerNight = cabin.querySelector('input[name="pricePerNight[]"]');
            const oldAdjustNight = cabin.querySelector('.oldAdjustNight');
            const oldPricePerWeek = cabin.querySelector('input[name="pricePerWeek[]"]');
            const oldAdjustWeek = cabin.querySelector('.oldAdjustWeek');

            priceAdjust(oldPricePerNight, oldAdjustNight);
            priceAdjust(oldPricePerWeek, oldAdjustWeek);
        });
    }
});

// -------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    // Booking Confirmation
    const confirmBooking = document.querySelector(".confirm-booking");
    const bookingButton = document.getElementById("booking-button");
    const confirmBookingButton = document.getElementById("confirm-booking-button");
    const cancelBookingButton = document.getElementById("cancel-booking-button");
    const bookingForm = document.getElementById("booking-form");
    if (bookingButton) {
        bookingButton.addEventListener("click", (event) => {
            event.preventDefault();
            // Phone and Email Error Message
            const phoneInput = document.getElementById("booking-phone");
            const emailInput = document.getElementById("booking-email");
            if (!bookingForm.checkValidity()) {
                bookingForm.reportValidity();
                phoneInput.addEventListener("input", () => {
                    phoneInput.setCustomValidity("");
                });
                phoneInput.addEventListener("invalid", () => {
                    phoneInput.setCustomValidity("Please enter a valid phone number");
                });
                emailInput.addEventListener("input", () => {
                    emailInput.setCustomValidity("");
                });
                emailInput.addEventListener("invalid", () => {
                    emailInput.setCustomValidity("Please enter a valid email");
                });
                return;
            };

            const arrival = document.getElementById("arrival").value;
            const departure = document.getElementById("departure").value;
            const displayArrival = arrival.split('-').reverse().join(' - ');
            const displayDeparture = departure.split('-').reverse().join(' - ');
            const cabin = document.getElementById("cabin_type").value;
            const guests = document.getElementById("number_of_guest").value;
            const firstName = document.getElementById("booking-first-name").value.trim();
            const lastName = document.getElementById("booking-last-name").value.trim();
            const phone = document.getElementById("booking-phone").value.trim();
            const email = document.getElementById("booking-email").value.trim();
            const message = document.getElementById("booking-message").value.trim();
            const bookedCabin = document.querySelector(".booked-cabin");
            const bookedArrival = document.querySelector(".booked-arrival");
            const bookedDeparture = document.querySelector(".booked-departure");
            const bookedName = document.querySelector(".booked-name");
            const bookedPhone = document.querySelector(".booked-phone");
            const bookedEmail = document.querySelector(".booked-email");
            const bookedGuest = document.querySelector(".booked-number-of-guest");
            const bookedMessage = document.querySelector(".booked-message");

            bookedCabin.innerHTML = `
        <p><strong>Cabin Type:</strong> ${cabin}</p>`;
            bookedArrival.innerHTML = `
        <p><strong>Arrival:</strong> ${displayArrival}</p>`;
            bookedDeparture.innerHTML = `
        <p><strong>Departure:</strong> ${displayDeparture}</p>`;
            bookedGuest.innerHTML = `
        <p><strong>Number of Guest:</strong> ${guests}</p>`;
            bookedName.innerHTML = `
        <p><strong>Full Name:</strong> ${firstName} ${lastName}</p>`;
            bookedPhone.innerHTML = `
        <p><strong>Phone Number:</strong> ${phone}</p>`;
            bookedEmail.innerHTML = `
        <p><strong>Email:</strong> ${email}</p>`;
            bookedMessage.innerHTML = `
        ${message ? `<p><strong>Message:</strong> ${message}</p>` : ''}
    `;
            confirmBooking.style.display = "flex";
        });
        // -------------------------------------
        confirmBookingButton.addEventListener("click", (event) => {
            event.preventDefault();
            const bookingForm = document.getElementById("booking-form");
            cancelBookingButton.style.display = "none";
            confirmBookingButton.innerHTML = "Confirmed";
            confirmBookingButton.style.background = "green";
            confirmBookingButton.style.color = "white";
            confirmBookingButton.disabled = true;
            bookingForm.submit();
        });
        // -------------------------------------  
        cancelBookingButton.addEventListener("click", () => {
            confirmBooking.style.display = "none";
        });
    }
});




