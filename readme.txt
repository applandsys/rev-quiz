=== REW QUIZZ ===
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 1.4.0
License: GPLv2 or later

Step-by-step quizzes with the category in the permalink, instruction text on every page, a social traffic-source filter and Ad Inserter style ad blocks.

== Description ==

* Quiz post type (REW QUIZZ > All Quizzes) with Quiz Categories.
* URLs: /quiz/{category-slug}/{quiz-slug}/ and one URL per question: /2/, /3/ ... The last one is the result screen.
* Every quiz page shows the quiz instructions (or the default from Settings) and the quiz's main text under the questions.
* Traffic filter (REW QUIZZ > Settings): tick one or more social networks and only visitors referred from them see the quiz.
  With nothing ticked, everyone sees it. Other visitors get a text-only page or a redirect.
  Matching uses the referrer, in-app browser, utm_source and the networks' click IDs (fbclid, ttclid ...).
  Allowed visitors are remembered with a signed cookie so later steps keep working.
* Ad blocks (REW QUIZZ > Ad blocks), modelled on the Ad Inserter plugin: 16 code blocks, each with
  - an insertion position: above header, before/after post, before/after content, before/after paragraph N,
    before/after image N, before/after excerpt, between posts, before comments, footer, popup, plus quiz positions
    (under the top bar, before/after the question title, after option N, after all options, result screen, quiz lists);
  - alignment (left, center, right, float, no wrapping or custom CSS) and an optional "Advertisement" label;
  - display on quiz pages, quiz lists, posts, pages, homepage, archives and search, and which quiz steps;
  - white/black lists for categories, tags, post IDs, URLs, URL parameters and referrers;
  - desktop / tablet / phone, logged-in or logged-out users, max insertions, min paragraphs;
  - manual insertion by shortcode [rq_ad block="N"] (pasted [adinserter block="N"] also works),
    the "REW QUIZZ ad block" widget and the PHP function rq_ad_block( N ).
  Also Header and Footer code, an ads.txt editor, the AdSense publisher ID (library loaded once),
  anchor position, and the Google H5 rewarded and interstitial formats.
  Updating from 1.3 turns the old AdSense settings into blocks 1-4 (quiz banner after option 4, result banner,
  quiz list banner, first-step popup) and moves the old header/footer code.
* OneSignal and Google Translate in REW QUIZZ > Settings.
* Activation creates 10 quiz categories (Movies & Cinema, TV Shows & Web Series, Music & Songs, Celebrities, Gaming,
  Sports, Funny & Memes, General Knowledge, Love & Relationships, Personality & Fun Quizzes) with 5 quizzes each
  (includes/demo-quizzes.php). Tick "Correct" on an option to make a trivia quiz with a score.
* It also creates the six categories of the original site and a demo quiz ("EMI Loan Planner")
  that rebuilds the original Language > Country > Profession > Currency > Annual Salary funnel.

== Theme overrides ==

Copy templates/single-quiz.php or templates/quiz-list.php to {your-theme}/rev-quiz/ to customise them.

== Notes ==

Pages that use the traffic filter send no-cache headers; exclude /quiz/ from any page cache or CDN cache.
