=== REW QUIZZ ===
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later

Step-by-step quizzes with the category in the permalink, instruction text on every page, a social traffic-source filter and one settings screen for ad code.

== Description ==

* Quiz post type (REW QUIZZ > All Quizzes) with Quiz Categories.
* URLs: /quiz/{category-slug}/{quiz-slug}/ and one URL per question: /2/, /3/ ... The last one is the result screen.
* Every quiz page shows the quiz instructions (or the default from Settings) and the quiz's main text under the questions.
* Traffic filter (REW QUIZZ > Settings): tick one or more social networks and only visitors referred from them see the quiz.
  With nothing ticked, everyone sees it. Other visitors get a text-only page or a redirect.
  Matching uses the referrer, in-app browser, utm_source and the networks' click IDs (fbclid, ttclid ...).
  Allowed visitors are remembered with a signed cookie so later steps keep working.
* Ad code: every value of the old assets/ads-config.js (publisher ID, banner/popup slots, formats, popup timing,
  rewarded and interstitial placements, anchor position) plus OneSignal, Google Translate and header/footer code.
* Activation creates 10 quiz categories (Movies & Cinema, TV Shows & Web Series, Music & Songs, Celebrities, Gaming,
  Sports, Funny & Memes, General Knowledge, Love & Relationships, Personality & Fun Quizzes) with 5 quizzes each
  (includes/demo-quizzes.php). Tick "Correct" on an option to make a trivia quiz with a score.
* It also creates the six categories of the original site and a demo quiz ("EMI Loan Planner")
  that rebuilds the original Language > Country > Profession > Currency > Annual Salary funnel.

== Theme overrides ==

Copy templates/single-quiz.php or templates/quiz-list.php to {your-theme}/rev-quiz/ to customise them.

== Notes ==

Pages that use the traffic filter send no-cache headers; exclude /quiz/ from any page cache or CDN cache.
