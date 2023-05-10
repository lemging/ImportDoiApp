Readme
=================
Here is how to run the application.


Create image
---------------
<ol>
    <li>You can download a finished docker image from the docker hub with command <code>todo</code></li>
</ol>    
OR
<ol>
    <li>You can clone the project to yourself with the command <code>https://github.com/lemging/ImportDoiApp.git</code></li>
    <li>(Optional) Enter the credentials into Dockerfile. If you don't do this, you can enter them each time you run the container</li>
    <li>In the project folder, run <code>docker build -t import_doi_app .</code>which creates an image named import_doi_app</li>
</ol>

Run image
---------------
<ol>
  <li>In case you entered the data in the previous step, run it with command <code>docker run -d -p 8081:80 -p 8082:443 -e import_doi_app</code>, where you can enter you own ports</li>
  <li>In case you did not enter the data, run it with <code>docker run -d -p 8081:80 -p 8082:443 -e "LOGIN=log" -e "PASSWORD=pass" -e "DOI_PREFIX=11.111" import_doi_app</code>, where you enter your own ports and account data</li>
</ol>

A container will be created and the site will run on <code>localhost:(the port you specified)</code>.
