<div class="footer-nav-area" id="footerNav">
    <style>
        .suha-footer-nav ul li {
            text-align: center;
            font-size: 10px; /* টেক্সট সাইজ */
        }

        .suha-footer-nav ul li a,
        .suha-footer-nav ul li .dark-mode-toggle {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #555; /* ডিফল্ট রঙ */
            text-decoration: none;
            transition: color 0.3s ease; /* ট্রানজিশন */
        }

        .suha-footer-nav ul li a i,
        .suha-footer-nav ul li .dark-mode-toggle i {
            font-size: 20px; /* আইকনের সাইজ সমান */
            margin-bottom: 4px; /* টেক্সট এবং আইকনের মধ্যে গ্যাপ */
            transition: color 0.3s ease, transform 0.3s ease;
        }

        /* ডার্ক মোড সক্রিয় থাকলে স্টাইল */
        .dark-mode-toggle i.active,
        .dark-mode-toggle span.active {
            color: #f39c12; /* উজ্জ্বল রঙ */
            transform: scale(1.2); /* হাইলাইট */
        }

        /* হোভার ইফেক্ট */
        .suha-footer-nav ul li a:hover i,
        .suha-footer-nav ul li .dark-mode-toggle:hover i,
        .suha-footer-nav ul li a:hover span,
        .suha-footer-nav ul li .dark-mode-toggle:hover span {
            color: #f39c12; /* হোভার করার সময় উজ্জ্বল রঙ */
        }


    </style>
    <div class="suha-footer-nav">
        <ul class="h-100 d-flex align-items-center justify-content-between ps-0 d-flex rtl-flex-d-row-r">
            <li><a href="{{route('home')}}"><i class="ti ti-home"></i><span>Home</span></a></li>
            <li><a href="{{ route('how.to.use') }}"><i class="ti ti-adjustments-horizontal"></i><span>User Guide</span></a></li>
            <li><a href="{{ route('lottery.index') }}"><i class="ti ti-bowl"></i><span>Lottery</span></a></li>
            <li><a href="{{ route('contact.support') }}"><i class="ti ti-wallet"></i><span>Support</span></a></li>
            <li><a href="{{ route('ticket') }}"><i class="ti ti-ticket"></i><span>Sheet</span></a></li>
            <li>
                <input type="checkbox" id="rokon" class="d-none">
                <label for="rokon" class="dark-mode-toggle d-flex flex-column align-items-center">
                    <i id="themeIcon" class="ti ti-moon"></i>
                    <span>Dark Mode</span>
                </label>
            </li>
        </ul>
    </div>

    <script>
        // থিম এবং আইকন ম্যানেজমেন্ট
        var rokonSwitch = document.getElementById('rokon');
        var themeIcon = document.getElementById('themeIcon');
        var themeText = document.querySelector('.dark-mode-toggle span');
        var currencyIcons = document.querySelectorAll('.currency-icon'); // সব currency-icon সিলেক্ট করা

        // থিম স্টোরেজ থেকে পাওয়া
        var currentTheme = localStorage.getItem('theme');

        // পেজ লোডের সময় সঠিক থিম এবং স্টাইল সেট করা
        if (currentTheme) {
            document.documentElement.setAttribute('theme-color', currentTheme);
            if (currentTheme === 'dark') {
                rokonSwitch.checked = true;
                themeIcon.classList.remove('ti-moon');
                themeIcon.classList.add('ti-sun', 'active');
                themeText.classList.add('active');

                // currency-icon এর জন্য থিম অ্যাপ্লাই
                currencyIcons.forEach(function (icon) {
                    icon.classList.add('active');
                });
            }
        }

        // থিম চেঞ্জ করার ফাংশন
        function switchTheme(e) {
            if (e.target.checked) {
                document.documentElement.setAttribute('theme-color', 'dark');
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.remove('ti-moon');
                themeIcon.classList.add('ti-sun', 'active');
                themeText.classList.add('active');

                // currency-icon এর জন্য থিম অ্যাপ্লাই
                currencyIcons.forEach(function (icon) {
                    icon.classList.add('active');
                });
            } else {
                document.documentElement.setAttribute('theme-color', 'light');
                localStorage.setItem('theme', 'light');
                themeIcon.classList.remove('ti-sun', 'active');
                themeIcon.classList.add('ti-moon');
                themeText.classList.remove('active');

                // currency-icon থেকে থিম রিমুভ
                currencyIcons.forEach(function (icon) {
                    icon.classList.remove('active');
                });
            }
        }

        // ইভেন্ট লিসেনার যোগ করা
        if (rokonSwitch) {
            rokonSwitch.addEventListener('change', switchTheme, false);
        }
    </script>



</div>
