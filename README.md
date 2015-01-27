# About
RustMan is a tool I developed based on the RCON interface of Rust servers (that's Rust the game by Facepunch). Instead of having to use binary modifications that would cause servers to become "modded" and adding way too much functionality, this mod can run on a completely different machine than the Rust server and does nothing Rust can't do as it only uses RCON commands and therefore already implemented mechanisms.

This version RustMan-php is the third iteration of an undescribable mess that I caused in PHP, I'm sorry. I will never again make such a large standalone program in PHP. The first one was tiny and bad and all crammed into a single file, the second one tried to resolve a really bad error in Rust RCON but failed miserably and the third and final PHP iteration tried to make some stable-ish version. It's stable until the server has more than 42 players connected which is the point when RCON packages are split up and cause huge problems. There's a fix for this but I'm not going to implement it because you're not supposed to use this project.

# Why??
Well fuck you, that's why. This is not for use and if you do use it, it's your own fault and nobody will help you.
This project is obsolete by the C# iteration and also the fact that Rust has been remade completely and therefore most commands used in this program are invalid now. Also colours.

# Your code sucks!
I know, shut up. I never meant for this crap to be visible to any other person than me so you better be happy that you have the honor of looking at this shit code.

# Why PHP, are you retarded?
All this started by me making a working RCON class in PHP to query my server for information and put it on my website. I then made a proof-of-concept manager that would log chat and spawn the occasional random airdrop. It all exploded from there. I added shit all the time until it's become the fat blob that it is today.

# What are the specs? How does it work? Are there instructions?
No. If you want to know stuff about this, dig into the code and try to figure it out. I'm sorry if you do.
