<?php
/**
 * Demo content: 10 categories with 5 quizzes each.
 *
 * Trivia questions are [ question, options, index of the correct option ].
 * Fun (personality) questions are [ question, options ] and have no right answer.
 *
 * @return array category slug => [ name, icon, colour, description, quizzes[] ]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'movies-cinema'           => array(
		'name'        => 'Movies & Cinema',
		'icon'        => '🎬',
		'color'       => '#FDF3DD',
		'description' => 'Bollywood blockbusters, Hollywood classics, famous dialogues and the Oscars. How well do you know the big screen?',
		'quizzes'     => array(
			array(
				'slug'        => 'bollywood-blockbusters-trivia',
				'title'       => 'Bollywood Blockbusters Trivia',
				'description' => 'From Sholay to 3 Idiots, test how well you remember the biggest hits of Hindi cinema.',
				'questions'   => array(
					array( 'Which film made the line "Mogambo khush hua" famous?', array( 'Sholay', 'Mr. India', 'Don', 'Deewaar' ), 1 ),
					array( 'Who played Gabbar Singh in Sholay?', array( 'Amitabh Bachchan', 'Dharmendra', 'Amjad Khan', 'Sanjeev Kumar' ), 2 ),
					array( 'Which 2009 film follows three engineering college friends?', array( '3 Idiots', 'Dil Chahta Hai', 'Rock On!!', 'Zindagi Na Milegi Dobara' ), 0 ),
					array( 'Who starred opposite Shah Rukh Khan in Dilwale Dulhania Le Jayenge?', array( 'Madhuri Dixit', 'Juhi Chawla', 'Rani Mukerji', 'Kajol' ), 3 ),
				),
			),
			array(
				'slug'        => 'hollywood-classics-quiz',
				'title'       => 'Hollywood Classics Quiz',
				'description' => 'Directors, catchphrases and iconic worlds: a quick tour of Hollywood favourites.',
				'questions'   => array(
					array( 'Who directed Titanic (1997)?', array( 'Steven Spielberg', 'James Cameron', 'Christopher Nolan', 'Ridley Scott' ), 1 ),
					array( 'Which film features the line "I\'ll be back"?', array( 'Die Hard', 'Rocky', 'The Terminator', 'Predator' ), 2 ),
					array( 'What is the name of the wizarding school in Harry Potter?', array( 'Hogwarts', 'Durmstrang', 'Beauxbatons', 'Ilvermorny' ), 0 ),
					array( 'Which film won the first Oscar for Best Animated Feature?', array( 'Toy Story', 'Finding Nemo', 'Monsters, Inc.', 'Shrek' ), 3 ),
				),
			),
			array(
				'slug'        => 'which-movie-genre-are-you',
				'title'       => 'Which Movie Genre Are You?',
				'description' => 'Action hero, rom-com lead or thriller mastermind? Answer three quick questions and find your genre.',
				'questions'   => array(
					array( 'Pick your perfect Friday night.', array( 'A big party with friends', 'Blanket, snacks and a movie', 'An escape room challenge', 'Stargazing somewhere quiet' ) ),
					array( 'Choose a movie snack.', array( 'Popcorn', 'Nachos', 'Chocolate', 'Fresh fruit' ) ),
					array( 'What is the best kind of ending?', array( 'Happily ever after', 'A shocking twist', 'An epic final battle', 'An open ending to think about' ) ),
				),
			),
			array(
				'slug'        => 'famous-movie-dialogues',
				'title'       => 'Famous Movie Dialogues',
				'description' => 'Can you match these legendary lines to the films that made them unforgettable?',
				'questions'   => array(
					array( '"May the Force be with you" comes from which series?', array( 'Star Trek', 'Star Wars', 'Avatar', 'Dune' ), 1 ),
					array( 'Which villain asks "Why so serious?"', array( 'Bane', 'Loki', 'The Joker', 'Thanos' ), 2 ),
					array( '"Kitne aadmi the?" is a dialogue from which film?', array( 'Sholay', 'Agneepath', 'Karan Arjun', 'Deewaar' ), 0 ),
					array( '"Here\'s looking at you, kid" is from which classic?', array( 'Gone with the Wind', 'The Godfather', 'Citizen Kane', 'Casablanca' ), 3 ),
				),
			),
			array(
				'slug'        => 'oscar-winners-quiz',
				'title'       => 'Oscar Winners Quiz',
				'description' => 'Best Picture, Best Song and record-breaking actors. How much do you know about the Academy Awards?',
				'questions'   => array(
					array( 'Which film won Best Picture at the 2020 Oscars?', array( '1917', 'Joker', 'Parasite', 'Once Upon a Time in Hollywood' ), 2 ),
					array( '"Naatu Naatu" won Best Original Song for which film?', array( 'RRR', 'Baahubali 2', 'Pushpa', 'KGF Chapter 2' ), 0 ),
					array( 'Which actor has won the most acting Oscars?', array( 'Meryl Streep', 'Katharine Hepburn', 'Jack Nicholson', 'Daniel Day-Lewis' ), 1 ),
					array( 'What is the official name of the Oscar award?', array( 'Golden Globe', 'Palme d\'Or', 'Golden Bear', 'Academy Award of Merit' ), 3 ),
				),
			),
		),
	),

	'tv-shows-web-series'     => array(
		'name'        => 'TV Shows & Web Series',
		'icon'        => '📺',
		'color'       => '#E2EEF5',
		'description' => 'Sitcoms, fantasy epics and the best Indian web series. Put your binge-watching to the test.',
		'quizzes'     => array(
			array(
				'slug'        => 'friends-trivia-quiz',
				'title'       => 'Friends Trivia Quiz',
				'description' => 'Could this BE any easier? Prove you are a true fan of the six friends from New York.',
				'questions'   => array(
					array( 'What is the name of the coffee shop in Friends?', array( 'Monk\'s Café', 'Central Perk', 'MacLaren\'s', 'The Max' ), 1 ),
					array( 'What is Ross Geller\'s profession?', array( 'Chef', 'Actor', 'Paleontologist', 'Lawyer' ), 2 ),
					array( 'Which character\'s catchphrase is "How you doin\'?"', array( 'Joey', 'Chandler', 'Ross', 'Phoebe' ), 0 ),
				),
			),
			array(
				'slug'        => 'game-of-thrones-quiz',
				'title'       => 'Game of Thrones Quiz',
				'description' => 'Houses, dragons and sharp one-liners from Westeros. Winter is coming for your score.',
				'questions'   => array(
					array( 'What are the words of House Stark?', array( 'Fire and Blood', 'Hear Me Roar', 'Winter Is Coming', 'We Do Not Sow' ), 2 ),
					array( 'How many dragons hatch for Daenerys?', array( 'Two', 'Three', 'Four', 'Five' ), 1 ),
					array( 'Who says "I drink and I know things"?', array( 'Tyrion Lannister', 'Jon Snow', 'Bronn', 'Jaime Lannister' ), 0 ),
				),
			),
			array(
				'slug'        => 'which-web-series-should-you-binge',
				'title'       => 'Which Web Series Should You Binge?',
				'description' => 'Tell us your mood and your watching style, and we will tell you what kind of series to start tonight.',
				'questions'   => array(
					array( 'What mood are you in tonight?', array( 'I want thrills', 'I want to laugh', 'I want to feel something', 'I want my mind blown' ) ),
					array( 'How long should an episode be?', array( 'Under 30 minutes', 'About an hour', 'Doesn\'t matter', 'Give me a short mini-series' ) ),
					array( 'Who are you watching with?', array( 'Just me', 'My partner', 'My friends', 'My family' ) ),
				),
			),
			array(
				'slug'        => 'indian-web-series-trivia',
				'title'       => 'Indian Web Series Trivia',
				'description' => 'Mirzapur, Panchayat and The Family Man: how closely were you watching?',
				'questions'   => array(
					array( 'Who plays Srikant Tiwari in The Family Man?', array( 'Pankaj Tripathi', 'Manoj Bajpayee', 'Nawazuddin Siddiqui', 'Jaideep Ahlawat' ), 1 ),
					array( 'Which series features the character "Kaleen Bhaiya"?', array( 'Sacred Games', 'Paatal Lok', 'Mirzapur', 'Panchayat' ), 2 ),
					array( 'In which village is Panchayat set?', array( 'Phulera', 'Malgudi', 'Rampur', 'Mirzapur' ), 0 ),
				),
			),
			array(
				'slug'        => 'sitcom-superfan-quiz',
				'title'       => 'Sitcom Superfan Quiz',
				'description' => 'From Dunder Mifflin to Gokuldham Society, see if you are a true sitcom superfan.',
				'questions'   => array(
					array( 'Which sitcom features Sheldon Cooper?', array( 'How I Met Your Mother', 'Seinfeld', 'The Big Bang Theory', 'Community' ), 2 ),
					array( 'What company do the characters of The Office (US) work for?', array( 'Dunder Mifflin', 'Vance Refrigeration', 'Sabre', 'Staples' ), 0 ),
					array( 'Who says "Legen... wait for it... dary!"?', array( 'Ted Mosby', 'Chandler Bing', 'Michael Scott', 'Barney Stinson' ), 3 ),
					array( 'In Taarak Mehta Ka Ooltah Chashmah, what is the society called?', array( 'Shanti Kunj', 'Gokuldham', 'Anand Vihar', 'Sai Baba Nagar' ), 1 ),
				),
			),
		),
	),

	'music-songs'             => array(
		'name'        => 'Music & Songs',
		'icon'        => '🎵',
		'color'       => '#FBE0E0',
		'description' => 'Singers, soundtracks, pop legends and instruments. Turn up the volume on your music knowledge.',
		'quizzes'     => array(
			array(
				'slug'        => 'guess-the-singer',
				'title'       => 'Guess the Singer',
				'description' => 'Hit songs from India and around the world. Can you name the voice behind each one?',
				'questions'   => array(
					array( 'Who sang "Tum Hi Ho" from Aashiqui 2?', array( 'Sonu Nigam', 'Arijit Singh', 'KK', 'Atif Aslam' ), 1 ),
					array( 'Who sings "Shape of You"?', array( 'Justin Bieber', 'Shawn Mendes', 'Harry Styles', 'Ed Sheeran' ), 3 ),
					array( 'Who sang the classic "Lag Jaa Gale"?', array( 'Lata Mangeshkar', 'Asha Bhosle', 'Shreya Ghoshal', 'Alka Yagnik' ), 0 ),
				),
			),
			array(
				'slug'        => 'what-music-matches-your-mood',
				'title'       => 'What Music Matches Your Mood?',
				'description' => 'Three quick picks reveal the soundtrack that fits your personality right now.',
				'questions'   => array(
					array( 'How does your ideal morning start?', array( 'Early workout', 'Slow coffee', 'Dancing while getting ready', 'Sleeping in' ) ),
					array( 'Pick a place to relax.', array( 'A beach', 'A mountain cabin', 'A busy city rooftop', 'A quiet library' ) ),
					array( 'Choose an instrument.', array( 'Drums', 'Guitar', 'Piano', 'Flute' ) ),
				),
			),
			array(
				'slug'        => '90s-bollywood-songs-quiz',
				'title'       => '90s Bollywood Songs Quiz',
				'description' => 'Take a trip back to the golden 90s and match the songs to their films and composers.',
				'questions'   => array(
					array( '"Chaiyya Chaiyya" is from which film?', array( 'Taal', 'Dil Se..', 'Dil To Pagal Hai', 'Rangeela' ), 1 ),
					array( '"Tujhe Dekha To" is from which film?', array( 'Kuch Kuch Hota Hai', 'Pardes', 'Dilwale Dulhania Le Jayenge', 'Hum Aapke Hain Koun..!' ), 2 ),
					array( 'Who composed the music of Roja (1992)?', array( 'A. R. Rahman', 'Ilaiyaraaja', 'Anu Malik', 'Nadeem-Shravan' ), 0 ),
				),
			),
			array(
				'slug'        => 'pop-music-legends',
				'title'       => 'Pop Music Legends',
				'description' => 'Kings, queens and bands that changed music forever. How many legends do you know?',
				'questions'   => array(
					array( 'Who is known as the "King of Pop"?', array( 'Elvis Presley', 'Prince', 'Michael Jackson', 'Freddie Mercury' ), 2 ),
					array( 'Who was the lead singer of Queen?', array( 'Freddie Mercury', 'Mick Jagger', 'Bono', 'Brian May' ), 0 ),
					array( 'In which year was the album Thriller released?', array( '1979', '1982', '1987', '1991' ), 1 ),
					array( 'Which city did The Beatles come from?', array( 'London', 'Manchester', 'Birmingham', 'Liverpool' ), 3 ),
				),
			),
			array(
				'slug'        => 'musical-instruments-quiz',
				'title'       => 'Musical Instruments Quiz',
				'description' => 'Keys, strings and rhythms: a quick quiz about instruments and the masters who played them.',
				'questions'   => array(
					array( 'How many keys does a standard piano have?', array( '76', '88', '92', '100' ), 1 ),
					array( 'Pandit Ravi Shankar was famous for playing which instrument?', array( 'Tabla', 'Sarod', 'Sitar', 'Flute' ), 2 ),
					array( 'How many strings does a standard guitar have?', array( '4', '5', '7', '6' ), 3 ),
					array( 'Ustad Zakir Hussain was a master of which instrument?', array( 'Tabla', 'Santoor', 'Veena', 'Shehnai' ), 0 ),
				),
			),
		),
	),

	'celebrities'             => array(
		'name'        => 'Celebrities',
		'icon'        => '🌟',
		'color'       => '#FFF4CC',
		'description' => 'Stars of Bollywood and Hollywood, famous couples and your dream celebrity dinner.',
		'quizzes'     => array(
			array(
				'slug'        => 'bollywood-star-trivia',
				'title'       => 'Bollywood Star Trivia',
				'description' => 'Nicknames, titles and TV shows: how well do you know the biggest Bollywood stars?',
				'questions'   => array(
					array( 'Which actor is popularly called "King Khan"?', array( 'Salman Khan', 'Shah Rukh Khan', 'Aamir Khan', 'Saif Ali Khan' ), 1 ),
					array( 'Which quiz show does Amitabh Bachchan host?', array( 'Kaun Banega Crorepati', 'Bigg Boss', 'Indian Idol', 'Dus Ka Dum' ), 0 ),
					array( 'In which year did Priyanka Chopra win Miss World?', array( '1994', '1997', '2000', '2004' ), 2 ),
				),
			),
			array(
				'slug'        => 'which-celebrity-are-you',
				'title'       => 'Which Celebrity Are You?',
				'description' => 'Your style, your weekend and your dream job reveal your inner celebrity.',
				'questions'   => array(
					array( 'Pick your red-carpet look.', array( 'Classic and elegant', 'Bold and colourful', 'Casual cool', 'Something nobody expects' ) ),
					array( 'How do you spend a free weekend?', array( 'At a party', 'Travelling somewhere new', 'Working on a passion project', 'At home with family' ) ),
					array( 'Choose your dream job.', array( 'Actor', 'Singer', 'Athlete', 'Entrepreneur' ) ),
				),
			),
			array(
				'slug'        => 'hollywood-celebrity-quiz',
				'title'       => 'Hollywood Celebrity Quiz',
				'description' => 'Real names, superhero roles and career beginnings of Hollywood\'s biggest names.',
				'questions'   => array(
					array( 'What is the real name of "The Rock"?', array( 'John Cena', 'Vin Diesel', 'Dwayne Johnson', 'Jason Statham' ), 2 ),
					array( 'Who played Iron Man in the Marvel films?', array( 'Robert Downey Jr.', 'Chris Evans', 'Chris Hemsworth', 'Mark Ruffalo' ), 0 ),
					array( 'Taylor Swift started her career in which genre?', array( 'Rock', 'Jazz', 'Hip-hop', 'Country' ), 3 ),
				),
			),
			array(
				'slug'        => 'celebrity-couples-quiz',
				'title'       => 'Celebrity Couples Quiz',
				'description' => 'Cricket meets Bollywood and Bollywood meets Hollywood. Match the famous couples.',
				'questions'   => array(
					array( 'Who is Virat Kohli married to?', array( 'Deepika Padukone', 'Anushka Sharma', 'Alia Bhatt', 'Katrina Kaif' ), 1 ),
					array( 'Who is Ranveer Singh married to?', array( 'Anushka Sharma', 'Kiara Advani', 'Deepika Padukone', 'Sara Ali Khan' ), 2 ),
					array( 'Which singer is married to Priyanka Chopra?', array( 'Nick Jonas', 'Joe Jonas', 'Justin Bieber', 'Shawn Mendes' ), 0 ),
				),
			),
			array(
				'slug'        => 'dream-celebrity-dinner',
				'title'       => 'Plan Your Dream Celebrity Dinner',
				'description' => 'Pick the guests, the place and the menu, and see what your dream dinner says about you.',
				'questions'   => array(
					array( 'Who is your first guest?', array( 'A movie star', 'A sports legend', 'A pop singer', 'A comedian' ) ),
					array( 'Where is the dinner?', array( 'A rooftop in Mumbai', 'A beach in Goa', 'A penthouse in New York', 'A castle in Europe' ) ),
					array( 'What is on the menu?', array( 'Biryani', 'Pizza', 'Sushi', 'Street food' ) ),
				),
			),
		),
	),

	'gaming'                  => array(
		'name'        => 'Gaming',
		'icon'        => '🎮',
		'color'       => '#E8E4FB',
		'description' => 'Console classics, mobile hits, esports and retro favourites. Press start!',
		'quizzes'     => array(
			array(
				'slug'        => 'video-game-legends',
				'title'       => 'Video Game Legends',
				'description' => 'Plumbers, creepers and consoles: a quick quiz about the most famous names in gaming.',
				'questions'   => array(
					array( 'What is the name of Mario\'s brother?', array( 'Wario', 'Luigi', 'Toad', 'Yoshi' ), 1 ),
					array( 'What colour is a Creeper in Minecraft?', array( 'Green', 'Red', 'Blue', 'Purple' ), 0 ),
					array( 'Which company makes the PlayStation?', array( 'Nintendo', 'Microsoft', 'Sony', 'Sega' ), 2 ),
				),
			),
			array(
				'slug'        => 'what-kind-of-gamer-are-you',
				'title'       => 'What Kind of Gamer Are You?',
				'description' => 'Casual, competitive or story explorer? Find out what type of gamer you really are.',
				'questions'   => array(
					array( 'How long is your usual gaming session?', array( 'A few minutes', 'About an hour', 'Hours and hours', 'Only on weekends' ) ),
					array( 'Which game type do you love most?', array( 'Battle royale', 'Puzzle', 'Story adventure', 'Sports' ) ),
					array( 'When you lose a match, you...', array( 'Play again right away', 'Watch tips online', 'Take a break', 'Blame the lag' ) ),
				),
			),
			array(
				'slug'        => 'mobile-games-quiz',
				'title'       => 'Mobile Games Quiz',
				'description' => 'Candy, clans and angry birds. Test what you know about the games on your phone.',
				'questions'   => array(
					array( 'What does BGMI stand for?', array( 'Battle Game Mobile India', 'Battlegrounds Mobile India', 'Best Gaming Mobile India', 'Battle Ground Multiplayer India' ), 1 ),
					array( 'Which company made Candy Crush Saga?', array( 'Supercell', 'Rovio', 'King', 'Zynga' ), 2 ),
					array( 'Which company made Clash of Clans?', array( 'Supercell', 'King', 'Epic Games', 'Riot Games' ), 0 ),
					array( 'Which company made Angry Birds?', array( 'King', 'Supercell', 'Gameloft', 'Rovio' ), 3 ),
				),
			),
			array(
				'slug'        => 'esports-quiz',
				'title'       => 'Esports Quiz',
				'description' => 'The studios behind the world\'s most competitive games. How pro is your knowledge?',
				'questions'   => array(
					array( 'Which company developed Fortnite?', array( 'Epic Games', 'Valve', 'Blizzard', 'Riot Games' ), 0 ),
					array( 'Which company developed League of Legends?', array( 'Valve', 'Riot Games', 'Blizzard', 'EA' ), 1 ),
					array( 'Which company developed Counter-Strike?', array( 'Ubisoft', 'Activision', 'Valve', 'EA' ), 2 ),
				),
			),
			array(
				'slug'        => 'retro-gaming-quiz',
				'title'       => 'Retro Gaming Quiz',
				'description' => 'Pellets, blue hedgehogs and falling blocks. A throwback quiz for classic gamers.',
				'questions'   => array(
					array( 'What does Pac-Man eat as he moves through the maze?', array( 'Coins', 'Pellets', 'Stars', 'Rings' ), 1 ),
					array( 'What colour is Sonic the Hedgehog?', array( 'Red', 'Green', 'Yellow', 'Blue' ), 3 ),
					array( 'In which country was Tetris created?', array( 'Japan', 'USA', 'Soviet Union', 'United Kingdom' ), 2 ),
					array( 'What type of Pokémon is Pikachu?', array( 'Electric', 'Fire', 'Water', 'Grass' ), 0 ),
				),
			),
		),
	),

	'sports'                  => array(
		'name'        => 'Sports',
		'icon'        => '⚽',
		'color'       => '#E7F6CE',
		'description' => 'Cricket, football, the Olympics and sporting legends. Step up and take your shot.',
		'quizzes'     => array(
			array(
				'slug'        => 'cricket-trivia',
				'title'       => 'Cricket Trivia',
				'description' => 'World Cups, legends and the basics of the game. Are you a true cricket fan?',
				'questions'   => array(
					array( 'How many players does a cricket team have on the field?', array( '9', '10', '11', '12' ), 2 ),
					array( 'Who is known as the "Master Blaster"?', array( 'Sachin Tendulkar', 'Virat Kohli', 'MS Dhoni', 'Rahul Dravid' ), 0 ),
					array( 'Which team did India beat in the 2011 World Cup final?', array( 'Australia', 'Pakistan', 'England', 'Sri Lanka' ), 3 ),
					array( 'Who won the first men\'s T20 World Cup in 2007?', array( 'Pakistan', 'India', 'Australia', 'West Indies' ), 1 ),
				),
			),
			array(
				'slug'        => 'football-fever-quiz',
				'title'       => 'Football Fever Quiz',
				'description' => 'Superstars, World Cups and the beautiful game. Kick off your football quiz.',
				'questions'   => array(
					array( 'How many players does a football team have on the pitch?', array( '11', '10', '9', '12' ), 0 ),
					array( 'Which country does Lionel Messi play for?', array( 'Brazil', 'Spain', 'Argentina', 'Portugal' ), 2 ),
					array( 'Which country hosted the 2022 FIFA World Cup?', array( 'Russia', 'Qatar', 'Brazil', 'USA' ), 1 ),
					array( 'Which country does Cristiano Ronaldo play for?', array( 'Spain', 'Italy', 'France', 'Portugal' ), 3 ),
				),
			),
			array(
				'slug'        => 'olympics-quiz',
				'title'       => 'Olympics Quiz',
				'description' => 'Rings, records and golden moments from the world\'s biggest sporting event.',
				'questions'   => array(
					array( 'How many rings are on the Olympic flag?', array( '4', '5', '6', '7' ), 1 ),
					array( 'In which event did Neeraj Chopra win gold at Tokyo 2020?', array( 'Javelin throw', 'Shot put', 'Long jump', '100 m sprint' ), 0 ),
					array( 'How often are the Summer Olympics held?', array( 'Every 2 years', 'Every 3 years', 'Every 5 years', 'Every 4 years' ), 3 ),
					array( 'Which city hosted the 2024 Summer Olympics?', array( 'London', 'Tokyo', 'Paris', 'Los Angeles' ), 2 ),
				),
			),
			array(
				'slug'        => 'which-sport-suits-you',
				'title'       => 'Which Sport Suits You?',
				'description' => 'Team player or solo star? Three questions to find the sport that matches your style.',
				'questions'   => array(
					array( 'Do you prefer playing...', array( 'In a big team', 'With one partner', 'On my own', 'Just watching' ) ),
					array( 'What gets you excited?', array( 'Speed', 'Strategy', 'Strength', 'Skill and precision' ) ),
					array( 'Pick a place to play.', array( 'A stadium', 'A beach', 'A mountain trail', 'A swimming pool' ) ),
				),
			),
			array(
				'slug'        => 'sports-legends-quiz',
				'title'       => 'Sports Legends Quiz',
				'description' => 'Sprinters, champions and icons. Match each legend to their country or sport.',
				'questions'   => array(
					array( 'Which country is Usain Bolt from?', array( 'USA', 'Jamaica', 'Kenya', 'Canada' ), 1 ),
					array( 'Which sport did Roger Federer play?', array( 'Badminton', 'Golf', 'Tennis', 'Squash' ), 2 ),
					array( 'Which sport made Michael Jordan famous?', array( 'Basketball', 'Baseball', 'American football', 'Ice hockey' ), 0 ),
					array( 'In which sport is Mary Kom a world champion?', array( 'Wrestling', 'Weightlifting', 'Judo', 'Boxing' ), 3 ),
				),
			),
		),
	),

	'funny-memes'             => array(
		'name'        => 'Funny & Memes',
		'icon'        => '😂',
		'color'       => '#FFE8D6',
		'description' => 'Silly questions, meme trivia and personality tests that do not take themselves seriously.',
		'quizzes'     => array(
			array(
				'slug'        => 'which-meme-are-you',
				'title'       => 'Which Meme Are You?',
				'description' => 'Your daily habits say a lot. Find out which internet meme is basically you.',
				'questions'   => array(
					array( 'Your alarm rings. You...', array( 'Snooze five times', 'Jump out of bed', 'Stare at the ceiling', 'Check memes first' ) ),
					array( 'Your group chat role is...', array( 'The one who sends memes', 'The one who plans', 'The silent reader', 'The one who replies days later' ) ),
					array( 'Pick a reaction.', array( 'LOL', 'Facepalm', 'Eye roll', 'Crying laughing' ) ),
				),
			),
			array(
				'slug'        => 'how-funny-are-you',
				'title'       => 'How Funny Are You?',
				'description' => 'Class clown or quiet wit? Three quick questions measure your sense of humour.',
				'questions'   => array(
					array( 'Your friends call you...', array( 'The comedian', 'The sarcastic one', 'The one who laughs at everything', 'The serious one' ) ),
					array( 'Your favourite type of joke is...', array( 'Puns', 'Dark humour', 'Silly slapstick', 'Memes only' ) ),
					array( 'Someone trips in public. You...', array( 'Help them up', 'Laugh, then help', 'Pretend you saw nothing', 'Record it (just kidding)' ) ),
				),
			),
			array(
				'slug'        => 'meme-trivia',
				'title'       => 'Meme Trivia',
				'description' => 'Doge, Rickrolls and Binod. How much internet culture do you really know?',
				'questions'   => array(
					array( 'Which dog breed is the famous "Doge" meme?', array( 'Corgi', 'Husky', 'Shiba Inu', 'Pug' ), 2 ),
					array( 'Which song is used to "Rickroll" people?', array( 'Never Gonna Give You Up', 'Take On Me', 'Africa', 'Don\'t Stop Believin\'' ), 0 ),
					array( 'The "Distracted Boyfriend" meme is originally a...', array( 'Movie scene', 'Stock photo', 'Painting', 'Cartoon' ), 1 ),
					array( 'Where did the "Binod" meme start?', array( 'A TV ad', 'A cricket match', 'A movie', 'YouTube comments' ), 3 ),
				),
			),
			array(
				'slug'        => 'your-reaction-gif-personality',
				'title'       => 'Your Reaction GIF Personality',
				'description' => 'Pick your reactions to everyday moments and discover the GIF that represents you.',
				'questions'   => array(
					array( 'Your food delivery is late. You...', array( 'Refresh the app every minute', 'Start cooking instead', 'Call the restaurant', 'Fall asleep waiting' ) ),
					array( 'Someone spoils your favourite show.', array( 'Dramatic gasp', 'Silent anger', 'Pretend it\'s fine', 'Unfollow them' ) ),
					array( 'It\'s finally Friday! You...', array( 'Do a happy dance', 'Nap immediately', 'Make big plans', 'Order pizza' ) ),
				),
			),
			array(
				'slug'        => 'whats-your-go-to-excuse',
				'title'       => 'What\'s Your Go-To Excuse?',
				'description' => 'We all have one. Answer honestly and find your signature excuse.',
				'questions'   => array(
					array( 'You are late again. You say...', array( 'Traffic was crazy', 'My alarm didn\'t ring', 'I was almost there', 'Wait, we said today?' ) ),
					array( 'You forgot a birthday. You...', array( 'Send a late meme', 'Blame the calendar app', 'Throw a surprise party later', 'Act like you remembered' ) ),
					array( 'You skipped the gym. Why?', array( 'Too tired', 'Rain', 'Tomorrow for sure', 'Rest day, obviously' ) ),
				),
			),
		),
	),

	'general-knowledge'       => array(
		'name'        => 'General Knowledge',
		'icon'        => '🌍',
		'color'       => '#DDF1F6',
		'description' => 'Capitals, science, history, geography and India GK. How smart are you really?',
		'quizzes'     => array(
			array(
				'slug'        => 'world-capitals-quiz',
				'title'       => 'World Capitals Quiz',
				'description' => 'Some capitals are trickier than you think. Can you name them all?',
				'questions'   => array(
					array( 'What is the capital of Australia?', array( 'Sydney', 'Melbourne', 'Canberra', 'Perth' ), 2 ),
					array( 'What is the capital of Canada?', array( 'Ottawa', 'Toronto', 'Vancouver', 'Montreal' ), 0 ),
					array( 'What is the capital of Japan?', array( 'Osaka', 'Tokyo', 'Kyoto', 'Hiroshima' ), 1 ),
					array( 'What is the capital of Brazil?', array( 'Rio de Janeiro', 'São Paulo', 'Salvador', 'Brasília' ), 3 ),
				),
			),
			array(
				'slug'        => 'science-basics-quiz',
				'title'       => 'Science Basics Quiz',
				'description' => 'Planets, molecules and the human body. Revisit the science you learned at school.',
				'questions'   => array(
					array( 'What is H2O more commonly called?', array( 'Salt', 'Water', 'Oxygen', 'Hydrogen' ), 1 ),
					array( 'Which planet is closest to the Sun?', array( 'Mercury', 'Venus', 'Earth', 'Mars' ), 0 ),
					array( 'How many bones are in an adult human body?', array( '186', '196', '216', '206' ), 3 ),
					array( 'Which gas do plants absorb from the air?', array( 'Oxygen', 'Nitrogen', 'Carbon dioxide', 'Helium' ), 2 ),
				),
			),
			array(
				'slug'        => 'india-gk-quiz',
				'title'       => 'India GK Quiz',
				'description' => 'National symbols, rivers and monuments. A quick general knowledge quiz about India.',
				'questions'   => array(
					array( 'What is the national animal of India?', array( 'Lion', 'Elephant', 'Bengal tiger', 'Peacock' ), 2 ),
					array( 'What is the national flower of India?', array( 'Lotus', 'Rose', 'Sunflower', 'Marigold' ), 0 ),
					array( 'In which city is the Taj Mahal?', array( 'Delhi', 'Agra', 'Jaipur', 'Lucknow' ), 1 ),
					array( 'Which is the longest river in India?', array( 'Yamuna', 'Godavari', 'Brahmaputra', 'Ganga' ), 3 ),
				),
			),
			array(
				'slug'        => 'history-quiz',
				'title'       => 'History Quiz',
				'description' => 'Leaders, wars, wonders and masterpieces. How well do you know the past?',
				'questions'   => array(
					array( 'Who was the first Prime Minister of India?', array( 'Jawaharlal Nehru', 'Sardar Patel', 'Mahatma Gandhi', 'Lal Bahadur Shastri' ), 0 ),
					array( 'In which year did World War II end?', array( '1939', '1942', '1945', '1950' ), 2 ),
					array( 'The Great Wall is in which country?', array( 'Japan', 'China', 'Mongolia', 'Korea' ), 1 ),
					array( 'Who painted the Mona Lisa?', array( 'Michelangelo', 'Raphael', 'Vincent van Gogh', 'Leonardo da Vinci' ), 3 ),
				),
			),
			array(
				'slug'        => 'geography-quiz',
				'title'       => 'Geography Quiz',
				'description' => 'Oceans, mountains, deserts and continents. Test your knowledge of our planet.',
				'questions'   => array(
					array( 'Which is the largest ocean on Earth?', array( 'Atlantic', 'Indian', 'Pacific', 'Arctic' ), 2 ),
					array( 'Which is the tallest mountain in the world?', array( 'Mount Everest', 'K2', 'Kangchenjunga', 'Mount Kilimanjaro' ), 0 ),
					array( 'Which is the largest hot desert in the world?', array( 'Thar', 'Gobi', 'Kalahari', 'Sahara' ), 3 ),
					array( 'How many continents are there?', array( '5', '7', '6', '8' ), 1 ),
				),
			),
		),
	),

	'love-relationships'      => array(
		'name'        => 'Love & Relationships',
		'icon'        => '❤️',
		'color'       => '#FCE4EC',
		'description' => 'Love languages, date ideas and your romantic side. Fun quizzes for every heart.',
		'quizzes'     => array(
			array(
				'slug'        => 'what-is-your-love-language',
				'title'       => 'What Is Your Love Language?',
				'description' => 'Words, time, gifts, help or hugs? Discover how you most like to give and receive love.',
				'questions'   => array(
					array( 'What makes you feel most appreciated?', array( 'Kind words', 'Quality time together', 'A thoughtful gift', 'Someone helping me out' ) ),
					array( 'After a long day you want...', array( 'A sweet message', 'A long talk', 'A small surprise', 'A warm hug' ) ),
					array( 'Your favourite way to show love is...', array( 'Saying it out loud', 'Planning time together', 'Giving presents', 'Doing things for them' ) ),
				),
			),
			array(
				'slug'        => 'perfect-date-idea-quiz',
				'title'       => 'Find Your Perfect Date Idea',
				'description' => 'Candlelight dinner or road trip? Answer three questions to plan your perfect date.',
				'questions'   => array(
					array( 'Pick a time of day.', array( 'Sunrise', 'Lazy afternoon', 'Sunset', 'Late night' ) ),
					array( 'Choose a setting.', array( 'Fancy restaurant', 'Picnic in the park', 'Adventure park', 'Cozy home movie night' ) ),
					array( 'What would make it perfect?', array( 'Great food', 'Deep conversation', 'Lots of laughter', 'A surprise' ) ),
				),
			),
			array(
				'slug'        => 'are-you-a-hopeless-romantic',
				'title'       => 'Are You a Hopeless Romantic?',
				'description' => 'Love letters, grand gestures and movie moments. How romantic are you, really?',
				'questions'   => array(
					array( 'Do you believe in love at first sight?', array( 'Absolutely', 'Maybe', 'Not really', 'Only in movies' ) ),
					array( 'Your idea of a grand gesture is...', array( 'A handwritten letter', 'A surprise trip', 'A song dedication', 'Remembering the little things' ) ),
					array( 'Pick a romantic movie moment.', array( 'Running to the airport', 'Dancing in the rain', 'A proposal on a mountain', 'A first kiss at midnight' ) ),
				),
			),
			array(
				'slug'        => 'which-rom-com-couple-are-you',
				'title'       => 'Which Rom-Com Couple Are You?',
				'description' => 'Every love story has a style. Find out which on-screen couple matches yours.',
				'questions'   => array(
					array( 'How did you (or would you) meet your partner?', array( 'Through friends', 'By total accident', 'At work or college', 'Online' ) ),
					array( 'Your couple vibe is...', array( 'Best friends', 'Opposites attract', 'Calm and steady', 'Always adventurous' ) ),
					array( 'You argue about...', array( 'Where to eat', 'Who is right', 'Nothing at all', 'Which movie to watch' ) ),
				),
			),
			array(
				'slug'        => 'what-kind-of-partner-are-you',
				'title'       => 'What Kind of Partner Are You?',
				'description' => 'The planner, the protector or the fun one? See what you bring to a relationship.',
				'questions'   => array(
					array( 'In a relationship you are usually...', array( 'The planner', 'The listener', 'The fun one', 'The protector' ) ),
					array( 'How do you handle a disagreement?', array( 'Talk it out right away', 'Take time to cool off', 'Make a joke to ease it', 'Write down my thoughts' ) ),
					array( 'Your ideal anniversary gift is...', array( 'A trip together', 'Something handmade', 'Jewellery or a watch', 'A day with no phones' ) ),
				),
			),
		),
	),

	'personality-fun-quizzes' => array(
		'name'        => 'Personality & Fun Quizzes',
		'icon'        => '🧠',
		'color'       => '#EDE7F6',
		'description' => 'Introvert or extrovert, your aura, your superpower and more. Get to know yourself.',
		'quizzes'     => array(
			array(
				'slug'        => 'introvert-or-extrovert',
				'title'       => 'Are You an Introvert or an Extrovert?',
				'description' => 'Where do you get your energy from? Three questions reveal your social style.',
				'questions'   => array(
					array( 'At a party you usually...', array( 'Talk to everyone', 'Stay with close friends', 'Find the pet in the room', 'Leave early' ) ),
					array( 'Your perfect weekend is...', array( 'Out with a big group', 'Brunch with one friend', 'A book and some quiet', 'A solo trip' ) ),
					array( 'After a busy day you recharge by...', array( 'Calling a friend', 'Going out', 'Being alone', 'Listening to music' ) ),
				),
			),
			array(
				'slug'        => 'whats-your-spirit-animal',
				'title'       => 'What\'s Your Spirit Animal?',
				'description' => 'Lion, owl, dolphin or wolf? Find the animal that matches your personality.',
				'questions'   => array(
					array( 'Which word describes you best?', array( 'Brave', 'Wise', 'Playful', 'Loyal' ) ),
					array( 'Pick a place to live.', array( 'Open grasslands', 'A quiet forest', 'Near the ocean', 'The mountains' ) ),
					array( 'Friends come to you for...', array( 'Courage', 'Advice', 'Fun', 'Support' ) ),
				),
			),
			array(
				'slug'        => 'what-color-is-your-aura',
				'title'       => 'What Color Is Your Aura?',
				'description' => 'Choose the things that feel most like you, and discover the colour of your energy.',
				'questions'   => array(
					array( 'Pick a season.', array( 'Summer', 'Spring', 'Autumn', 'Winter' ) ),
					array( 'Choose a gemstone.', array( 'Ruby', 'Emerald', 'Sapphire', 'Amethyst' ) ),
					array( 'Which word feels most like you?', array( 'Passion', 'Growth', 'Calm', 'Mystery' ) ),
				),
			),
			array(
				'slug'        => 'which-superpower-should-you-have',
				'title'       => 'Which Superpower Should You Have?',
				'description' => 'Flying, invisibility or reading minds? Find the superpower that suits you best.',
				'questions'   => array(
					array( 'What would you do with a free day?', array( 'Travel somewhere far', 'Solve a big problem', 'Help someone in need', 'Relax and do nothing' ) ),
					array( 'Your biggest strength is...', array( 'Courage', 'Intelligence', 'Kindness', 'Creativity' ) ),
					array( 'Pick a hero sidekick.', array( 'A loyal dog', 'A smart robot', 'A wise mentor', 'A funny best friend' ) ),
				),
			),
			array(
				'slug'        => 'which-city-matches-your-personality',
				'title'       => 'Which City Matches Your Personality?',
				'description' => 'Busy metro or calm hill town? Answer three questions to find your city match.',
				'questions'   => array(
					array( 'Your ideal pace of life is...', array( 'Fast and exciting', 'Balanced', 'Slow and peaceful', 'Always changing' ) ),
					array( 'Pick a weekend activity.', array( 'Shopping and cafés', 'Museums and history', 'Hiking and nature', 'Beaches and sunsets' ) ),
					array( 'Choose a street food.', array( 'Vada pav', 'Pani puri', 'Momos', 'Tacos' ) ),
				),
			),
		),
	),
);
