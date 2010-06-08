<?php

/* HangMan
An example demonstrating a full game of HangMan
Difficulty level: Intermediate */

require_once('/PATH/TO/NOLOH');

class Index extends WebPage
{
	function Index()
	{
		parent::WebPage('The Classic Game of HangMan -- programmed in NOLOH');
		// Add NOLOH logo and text label here so they are persistent throughout the application
		$this->Controls->Add(new Image('Images/NOLOHLogo.gif', 15, 15));
		$this->Controls->Add($title = new Label('HangMan', 17, 60, 200, 50));
		/* Sets the font via the CSS_ syntactical sugar. Any property available in CSS can be set
		   on a NOLOH Control by prepending it with CSS. */
		$title->CSSFont = '30px verdana';
		// Add Hangman
		$this->Controls->Add(new HangMan(0, 80, 850, 750));
	}
}
?>
