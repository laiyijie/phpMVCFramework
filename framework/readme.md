1、please modify the configuration of the apache server

	1) open the rewrite_module
	e.g.
		LoadModule rewrite_module modules/mod_rewrite.so
	
	2) writing the rewrite rules ( all the request to "/framework/.*" redirect to "/framework/index.php" )
	e.g.
		<Directory "c:/wamp/www/">
			<IfModule mod_rewrite.c>
			    RewriteEngine on
			    RewriteBase /
			    RewriteCond %{REQUEST_URI} ^/framework[$(/.*$)] [NC]
			    RewriteRule  ^/?(.*)$ /framework/index.php?%{QUERY_STRING} [END,NC]
			</IfModule>
		    Options Indexes FollowSymLinks
		    AllowOverride all
		    Require all granted
		</Directory>

	3) restart the apache server

2、open http://127.0.0.1/framework/info/test in your browser you will get the result {"status":"done","message":"testing"}

congratulations!

Author: Jason Lai
Email: JasonLai009@163.com

