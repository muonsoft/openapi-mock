package responder

const errorTemplate = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{title}}</title>
</head>
<body>
<h1>{{title}}</h1>
<p>{{message}}</p>
<p>
	If you see this message, please make an issue at project page 
	<a href="https://github.com/swagger-mock/swagger-mock/issues">https://github.com/swagger-mock/swagger-mock/issues</a>.
</p>
</body>
</html>
`
