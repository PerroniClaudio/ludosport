<x-website-layout>
    <div class="grid grid-cols-12 gap-x-3 px-8 pb-16  container mx-auto max-w-7xl">
        <section class="col-span-12 py-12">
            <h1
                class="text-6xl font-bold tracking-tighter sm:text-5xl xl:text-6xl/none pb-2 bg-clip-text text-transparent bg-gradient-to-r from-primary-600 to-primary-300">
                {{ __('website.cookie_policy_title') }}
            </h1>

            <div class="text-background-800 dark:text-background-200 text-justify">
                <p><b>Last updated: 25/03/2025</b></p>
                <p>This Cookie Policy describes the use of technical cookies on our website.</p>
                <h2 class="text-2xl font-semibold mt-6 mb-4">1. What are cookies?</h2>
                <p class="mb-4">
                    Cookies are small text files that are stored on the user's device when visiting a website. These files allow the site to recognize the user, ensure the proper functioning of the platform, and improve the browsing experience.
                </p>

                <h2 class="text-2xl font-semibold mt-6 mb-4">2. Types of cookies used</h2>
                <p class="mb-4">
                    Our website exclusively uses technical cookies, which are necessary for the operation of the site and managed through the Laravel framework. Specifically:
                </p>
                <ul class="list-disc list-inside mb-4">
                    <li><b>Laravel Session Cookies:</b> Used to manage user sessions and ensure consistent navigation across site pages.</li>
                    <li><b>CSRF Token Cookie:</b> Used to protect the site from cross-site request forgery (CSRF) attacks and ensure transaction security.</li>
                </ul>
                <p class="mb-4">
                    These cookies do not collect personal information and are not used for profiling or analysis purposes.
                </p>

                <h2 class="text-2xl font-semibold mt-6 mb-4">3. Third-Party Cookies</h2>
                <p class="mb-4">
                    This site does not use third-party cookies for tracking, analysis, or profiling purposes.
                </p>

                <h2 class="text-2xl font-semibold mt-6 mb-4">4. Cookie Management</h2>
                <p class="mb-4">
                    Since our site only uses technical cookies, explicit user consent is not required for their use. However, users can configure their browser to block or delete cookies. Disabling technical cookies may impair the proper functioning of the site.
                </p>
                <p class="mb-4">
                    Below are links to guides for managing cookies in major browsers:
                </p>
                <ul class="list-disc list-inside mb-4">
                    <li><a href="https://support.google.com/chrome/answer/95647" class="text-primary-600 hover:underline">Google Chrome</a></li>
                    <li><a href="https://support.mozilla.org/en-US/kb/block-websites-storing-cookies-site-data-firefox" class="text-primary-600 hover:underline">Mozilla Firefox</a></li>
                    <li><a href="https://support.apple.com/it-it/guide/safari/sfri11471/mac" class="text-primary-600 hover:underline">Apple Safari</a></li>
                    <li><a href="https://support.microsoft.com/en-us/windows/manage-cookies-in-microsoft-edge-view-allow-block-delete-and-use-168dab11-0753-043d-7c16-ede5947fc64d#:~:text=view%20that%20site.-,Open%20Edge%20browser%2C%20select%20Settings%20and%20more%20in%20the%20upper,recommended)%20to%20block%20all%20cookies." class="text-primary-600 hover:underline">Microsoft Edge</a></li>
                </ul>

                <h2 class="text-2xl font-semibold mt-6 mb-4">5. Data Retention</h2>
                <p class="mb-4">
                    The technical cookies used by the site have a limited lifespan. Specifically:
                </p>
                <ul class="list-disc list-inside mb-4">
                    <li><b>Session cookies:</b> Automatically deleted when the browser is closed.</li>
                    <li><b>Other technical cookies:</b> May have a predefined variable duration but are managed internally without being shared with third parties.</li>
                </ul>

                <h2 class="text-2xl font-semibold mt-6 mb-4">6. Changes to the Cookie Policy</h2>
                <p class="mb-4">
                    We reserve the right to update this Cookie Policy at any time. Users will be informed of significant changes through a notice on the website.
                </p>


            </div>
        </section>
    </div>
</x-website-layout>
