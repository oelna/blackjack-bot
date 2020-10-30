# Blackjack-Bot
A Twitch.TV chatbot that can play blackjack! Use with eg. Nightbot.

## Installation for channel admins

Upload the files to a server that supports PHP. The users in chat will not see any server info.

Make a new Nightbot custom command and call the URL where you're hosting `blackjack.php`:  
`$(urlfetch https://yourdomain.com/blackjack.php?user1=$(user)&user2=$(1)&command=$(2))`

That's it. That's the installation.

## How to play

In your chat, use the following syntax:

`!blackjack <username> <command>`

The username is always the name of the user you're playing against.

To start a new match against user `selimhex`, type  
`!blackjack selimhex new`

Draw cards with `hit`, like so:  
`!blackjack selimhex hit`

When you're confident you have drawn enough, you can `stand`. You will not be able to draw further cards!  
`!blackjack selimhex stand`

If you'd just like to know the current standings, use `status`:  
`!blackjack selimhex status`

And to show very basic usage of the bot, enter `help`:  
`!blackjack selimhex help`

## Feedback and improvements

I welcome input! I don't know much about Blackjack (I know!) and if somebody finds this and knows how to make it better, let me know, write an issue or PR.
If you like this and want to support my open source work, feel free to give back via my Patreon or Paypal. Any amount helps and is appreciated!
