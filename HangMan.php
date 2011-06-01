<?php
class HangMan extends Panel
{
	// A Group of RadioButtons which contains all possible difficulties
	private $DifficultyGroup;
	// A Label letting the user know how close he is to losing
	private $ChancesLeftLabel;
	/* A Label representing the user's current knowledge of the word, e.g.,
	 what he has opened and what he has left to guess*/
	private $GuessWordLabel;
	// The number of bad guesses already made by the user, defaults to 0
	private $BadGuesses = 0;
	/* A number representing the number of chances the user has to guess 
	the word. This will be set based on the selected difficulty*/
	private $Chances;
	// The word that the user will be trying to guess
	private $GuessWord;
	//Constructor
	function Hangman($left, $top, $width, $height)
	{
		parent::Panel($left, $top, $width, $height);
		// A Group of RadioButtons allows only one RadioButton to be Checked
		$this->DifficultyGroup = new Group();
		/* Each of the next three lines adds a new RadioButton Control 
		to the Group. A new Item is passed into the RadioButton so that  a 
		certain Text will be displayed while the Value will correspond to the 
		number of Chances that that difficulty allows.*/
		$this->DifficultyGroup->Add(new RadioButton(new Item('Easy game; you are allowed 10 misses.', 10), 15, 98, 300));
		$this->DifficultyGroup->Add(new RadioButton(new Item('Medium game; you are allowed 5 misses.', 5), 15, 121, 300));
		$this->DifficultyGroup->Add(new RadioButton(new Item('Hard game; you are only allowed 3 misses.', 3), 15, 144, 300));
		//Play button which starts the game
		$play = new Button('Play', 15, 169, 60);
		//Assign a Click Event to the play button
		$play->Click = new ServerEvent($this, 'PlayGame');
		//label to display HangMan instructions
		$explanation = new Label('This is the game of HangMan. You must guess a word or phrase, a letter at a time. If you make too many mistakes, you lose the game!', 15, 60, 620, 40);
		//AddRange allows you to add an multiple Control in a single statement
		$this->Controls->AddRange($explanation, $this->DifficultyGroup, $play);
	}
	function PlayGame()
	{
		// Validation to make sure one of the RadioButtons was Selected
		if($this->DifficultyGroup->SelectedValue != null)
		{
			/*Clearing the Controls removes from the screen everything that
			has been added to this Panel so far*/
			$this->Controls->Clear();
			/* Sets the number of chances based on the selected difficulty. 
			It will be 10, 5, or 3 because those were the three Values of the 
			Items passed into the difficulty RadioButtons when they were 
			instantiated.*/
			$this->Chances = $this->DifficultyGroup->SelectedValue;	
			//Create more necessary Controls
			$makeGuess = new Label('Please Make a Guess:', 15, 60, 200);
			$this->ChancesLeftLabel = new Label("You have made {$this->BadGuesses} bad guesses out of a maximum of {$this->Chances}.", 15, 178, 350);
			$giveUp = new Link(null, 'Give up', 15, 250, 60);
			$giveUp->Click = new ServerEvent($this, 'EndGame', 'You Lose!');
			$guess = new Label('Guess:', $this->ChancesLeftLabel->Left, $this->ChancesLeftLabel->Bottom + 20, 60);
			//Add these new Controls
			$this->Controls->AddRange($makeGuess, $this->ChancesLeftLabel, $giveUp, $guess);		
			// Create Labels for all the letters from A to Z 
			$this->CreateLetters();
			// Get a random word that the user will try to guess
			$this->GetWord();
			// Automatically call VannaWhite to open all the spaces of the word
			$this->VannaWhite('-');
		}
		else
			System::Alert('You must choose a difficulty level before playing.');
	}
	function CreateLetters()
	{
		//Generate Range of Letters
		$letters = range('A', 'Z');
		// Start with a left of 70 pixels
		$left = 70;
		// Iterate through letters
		foreach($letters as $letter)
		{
			/*Create and add a Label for each letter at the current left, and 
			increases the left by 15 pixels for the next letter*/
			$this->Controls->Add($char = new Label($letter, $left += 15, $this->ChancesLeftLabel->Bottom + 20, 15));
			$char->Click = new ServerEvent($this, 'MakeGuess', $char);
		}
	}
	function GetWord()
	{
		// Reads in the contents of the words.txt file, and splits it by return characters to create an array of words
		$words = explode("\n", file_get_contents('words.txt'));
		// Picks a random word from the array
		$this->GuessWord = $words[rand(0, count($words)-1)];
		// Replaces all the spaces by dashes and capitalizes it
		$this->GuessWord = strtoupper(str_replace(' ', '-', $this->GuessWord));
		/* Creates a Label representing the user's knowledge of the word so far. It has underscores for each of the
		   characters because so far no guesses were performed, and they are separated by spaces for aesthetics. */
		$this->GuessWordLabel = new Label(str_repeat(' _', strlen($this->GuessWord)), 15, 120, null, null);
		$this->GuessWordLabel->CSSLineHeight = '1.2';
		$this->Controls->Add($this->GuessWordLabel);
	}
	function VannaWhite($letter)
	{
		// This function will iterate through the word, open up a certain letter, and return whether that letter was in the word
		$wasFound = false;
		/* Starts at the first position, and keeps iterating while the guessed letter is in the word. Each time it iterates, it saves
		   the position, and next time i only iterates the rest of the word, i.e., after that position. */
		for($pos = 0; ($pos=strpos($this->GuessWord,$letter,$pos))!==false; ++$pos)
		{
			// Stores the Text in a local variable
			$text = $this->GuessWordLabel->Text;
			/* Replaces the underscore with the actual letter. 
			   The position is multiplied by 2 to account for the empty spaces that were added to look nicer. */
			$text[$pos*2 + 1] = $letter;
			// Sets the Text back to the local variable, with the underscore replaced
			$this->GuessWordLabel->Text = $text;
			// Remembers that the letter was found somewhere in the word
			$wasFound = true;
		}
		return $wasFound;
	}
	function MakeGuess($letter)
	{	
		// Attempts to call VannaWhite to open up the guessed letter
		if(!$this->VannaWhite($letter->Text))
			// If that letter was not found, increase BadGuesses, and notify the user that he is closer to losing
			$this->ChancesLeftLabel->Text = "You have made " . ++$this->BadGuesses . " bad guesses out of a maximum of $this->Chances.";
		// Make the guessed letter Label invisible, so that the user cannot keep guessing it
		$letter->Visible = false;
		
		// If the user has made too many bad guesses, they lose
		if($this->BadGuesses == $this->Chances)
			$this->EndGame('You Lost!');
		// Otherwise, if no underscores were found, or equivalently, all letters were guessed, they win
		elseif(strpos($this->GuessWordLabel->Text, '_') === false)
			$this->EndGame('You Win!');
	}
	function EndGame($text)
	{
		$this->Controls->Clear();
		// Lets the user know whether they won or lost
		$this->Controls->Add(new Label($text, 15, 60, 100));
		// Lets the user know what the word was
		$this->Controls->Add(new Label("The word/phrase was: $this->GuessWord", 15, 100, System::Auto));
		// Allow the user to play again
		$this->Controls->Add($reset = new Link(null, 'Play Again', 15, 140, 100));
		// When the Link is clicked, the static function Application::Reset will be called, which starts everything over again
		$reset->Click = new ServerEvent('Application', 'Reset');
	}
}
?>