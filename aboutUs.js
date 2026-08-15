document.addEventListener("DOMContentLoaded", function () {

    console.log("aboutUs.js is working");


    /* =====================================================
       GET ELEMENTS
    ===================================================== */

    const contactBtn = document.getElementById("contactBtn");

    const contactModal = document.getElementById("contactModal");

    const closeContactModal =
        document.getElementById("closeContactModal");

    const cancelContactModal =
        document.getElementById("cancelContactModal");

    const contactModalBackdrop =
        document.getElementById("contactModalBackdrop");



    /* =====================================================
       OPEN MODAL
    ===================================================== */

    function openModal() {

        if (!contactModal) {
            console.error("Contact modal was not found.");
            return;
        }

        contactModal.classList.remove("hidden");

        contactModal.setAttribute(
            "aria-hidden",
            "false"
        );

        document.body.classList.add("modal-open");

        console.log("Contact modal opened.");

    }



    /* =====================================================
       CLOSE MODAL
    ===================================================== */

    function closeModal() {

        if (!contactModal) {
            return;
        }

        contactModal.classList.add("hidden");

        contactModal.setAttribute(
            "aria-hidden",
            "true"
        );

        document.body.classList.remove("modal-open");

        console.log("Contact modal closed.");

    }



    /* =====================================================
       CONTACT US BUTTON
    ===================================================== */

    if (contactBtn) {

        contactBtn.addEventListener(
            "click",
            function (event) {

                event.preventDefault();

                openModal();

            }
        );

    } else {

        console.error(
            "ERROR: #contactBtn was not found."
        );

    }



    /* =====================================================
       CLOSE X
    ===================================================== */

    if (closeContactModal) {

        closeContactModal.addEventListener(
            "click",
            function () {

                closeModal();

            }
        );

    }



    /* =====================================================
       CLOSE BUTTON
    ===================================================== */

    if (cancelContactModal) {

        cancelContactModal.addEventListener(
            "click",
            function () {

                closeModal();

            }
        );

    }



    /* =====================================================
       CLICK BACKDROP TO CLOSE
    ===================================================== */

    if (contactModalBackdrop) {

        contactModalBackdrop.addEventListener(
            "click",
            function () {

                closeModal();

            }
        );

    }



    /* =====================================================
       ESC KEY TO CLOSE
    ===================================================== */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape" &&
                contactModal &&
                !contactModal.classList.contains("hidden")
            ) {

                closeModal();

            }

        }
    );



    /* =====================================================
       SIDEBAR ACTIVE LINK
    ===================================================== */

    let currentPage =
        window.location.pathname
            .split("/")
            .pop();


    /*
       If the page is opened directly and pathname is empty,
       treat it as index.html.
    */

    if (!currentPage) {
        currentPage = "index.html";
    }


    document
        .querySelectorAll(".sidebar-link")
        .forEach(function (link) {

            const href =
                link.getAttribute("href");

            if (href === currentPage) {

                link.classList.add("active");

            }

        });

});