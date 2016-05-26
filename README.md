# N_Mgmt - v3
## Purpose

I'll upload things here soon…

I'm currently cleanifying my code to be able to share it.

## What's new ?
#### LOT of things !
##### Better coding and folders/files organization :
  - Cleaner and easier to modify :
    - One php file = One functionnal part
    - The main core calls the others functionnal parts only if necessary
    - New features can be added faster
    - Dependencies folders should be in the same folder than N_Mgmt
  - Client's side JavaScripts are now using Ajax requests to make things faster between server and client

##### New dependencies :
  - FineDiff (v0.6) for differential modifications calculation on file backup
  - noUiSlider (v8.3.0) for… sliders

##### Complete interface rebuild :
  - Smooth menu opening by clicking the top-left logo
  - Sizeable rows ( 32px to 128px )
  - Choose between images icons or lightweight ( font-Awesome ) icons
  - Easy way to make your own theme
  - Better thumbnails management : enhancements on the "Resize_Images" function
  - Full-screen thumbnails display added with parameterization of the images sizes by a slider
  - …

##### New features :
  - User's options are stored in an ini file so that they don't depend on the php-session
  - Ability to upload folders structure if you're using webkit ( thanks to plupload configuration )
  - Uploaded images can now be Tiny-fied if you've got a TinyPNG account.
  - Ability to download a folder's content as a zip file
  - Option to create a backup when modifying a file
  - File backup can be used to see differential modifications
  - Options and users management have been re-thought :
    - Use of .ini files to make things easier
    - More options are available
  - Corrections are made for french typography when editing as article : non-breaking space before [?;:!] and between «  »
    -> This auto-correction should be made optionnal for non-french users
  - …


## Preview
![alt tag](https://raw.githubusercontent.com/NTakit/N_Mgmt/master/preview-3.x.png)


## More history

[Version 2](https://github.com/NTakit/N_Mgmt/blob/_old_v2/README.md)

[Version 1](https://github.com/NTakit/N_Mgmt/blob/_old_v1/README.md)
