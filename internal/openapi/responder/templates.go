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
<p>{{hint}}</p>
</body>
</html>
`

const errorHint = `
If you see this message, please investigate your specification for errors. 
If it seems to be a problem with the application, then make an issue at the project page 
<a href="https://github.com/muonsoft/openapi-mock/issues">https://github.com/muonsoft/openapi-mock/issues</a>.
Thank you for your support.
`

const unsupportedHint = `
If you want this feature to be supported, please make an issue at the project page 
<a href="https://github.com/muonsoft/openapi-mock/issues">https://github.com/muonsoft/openapi-mock/issues</a>.
Thank you for your support.
`
