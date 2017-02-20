## Contributing to paragraphs CSS code

Paragraphs are currently using Gulp and SASS tools for more efficient CSS development. For the people that wants to contribute to paragraphs CSS code you have two options:

1. If you want to propose CSS improvement but do not want to use our Gulp/SASS toolchain then just change compiled CSS and create a issue with a patch from it. When patch is accepted we will then transfer your changes to SASS and recompile CSS files.

2. Instead of manually changing CSS files, recommended way is to reuse our Gulp/SASS process and do changes in appropriate SASS files and then recompile CSS.


## Preparing your development environment for Gulp/SASS toolchain

If you want to do __step 2.__ but do not have needed Gulp/SASS experience do not worry, process is not that difficult and is explained in next steps:

- First thing you need to have is nodejs server on your machine. Please check http://@todo.org and follow steps of nodejs server installation for your operating system.

- Then change directory to paragraphs CSS folder

  `$ cd paragraphs/css`

- Before compiling SASS files with gulp you need to install required dependencies with node package manager tool. In the same folder execute

  `$ npm install`

  The list of dependencies are defined in `paragraphs/css/package.json` JSON file.

- You are now able to compile paragraphs CSS from our SASS source files. In the same folder execute

  `$ gulp`

If you did not get any error your local machine is now ready and with last command you already compiled paragraphs SASS files to CSS.

For closer look at our Gulp configuration and tasks check paragraphs/css/gulpfile.js.


## Doing changes in CSS over SASS

Now you are ready to do necessary changes to paragraphs CSS. First locate the CSS selector rule you want to change in CSS and then locate this rule in appropriate SASS file. Do the change in SASS file, save it and just execute again `$ gulp` from your console.

When you are satisfied with result in CSS files, create Drupal paragraphs issue and a patch in standard way.


If you are getting some warning when executing `$ gulp` like

> csslint: There are x problems in ../paragraphs/css/paragraphs.widget.css.

This warnings are coming from gulp csslint plugin which is doing statical checking of generated CSS files and this simply means that generated CSS code is not compatible with paragraphs CSS coding standards.
If warnings that are reported make sense then please try to change them and recompile CSS with Gulp again.


SASS is a very powerful tool and its always good option to know your tools better. Please check @todo-link powerfullfor more information on SASS syntax.
