The website formerly known as Hoipost.com
=======

(Mostly) intact source for my defunct web app.

In my opinion the most interesting part is the 'object' component system. Each restaurant/event/landmark/etc. is modeled as a generic object with components attached that encapsulate different types of information and functionality. I adapted this concept from the Component System pattern for game development (see: http://cowboyprogramming.com/2007/01/05/evolve-your-heirachy/), and it was by far the most flexible and powerful part of the entire Hoipost platform from a technical standpoint.

The rest of the code falls somewhere between uninteresting and ghastly. Pretty much anything that touches the administration 'dashboard' is awful and kludgey. Most of this was technical debt accumulated as I tried to get something that kind-of-worked out the door to test against the market.
