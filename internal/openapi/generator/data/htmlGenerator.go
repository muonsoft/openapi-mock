package data

import (
	"strings"
	"syreclabs.com/go/faker"
)

type htmlGenerator struct {
	random randomGenerator
}

const htmlTemplate = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{title}}</title>
</head>
<body>
	{{body}}
</body>
</html>
`

func (generator *htmlGenerator) GenerateHTML(_ int, _ int) string {
	lorem := faker.Lorem()

	title := lorem.Sentence(generator.random.Intn(9) + 3)
	body := "<h1>" + title + "</h1>"
	sectionsCount := generator.random.Intn(3) + 6

	for i := 0; i < sectionsCount; i++ {
		body += "<h2>" + lorem.Sentence(generator.random.Intn(9)+3) + "</h2>"
		paragraphsCount := generator.random.Intn(1) + 5

		for j := 0; j < paragraphsCount; j++ {
			body += "<p>" + lorem.Paragraph(generator.random.Intn(9)+3) + "</p>"
		}
	}

	template := strings.Replace(htmlTemplate, "{{title}}", title, 1)

	return strings.Replace(template, "{{body}}", body, 1)
}
