package serializer

import (
	"github.com/stretchr/testify/assert"
	"testing"
)

func TestXmlSerializer_Serialize_SerializableData_SerializedData(t *testing.T) {
	tests := []struct {
		name         string
		data         interface{}
		expectedData string
	}{
		{
			"map data",
			map[string]interface{}{
				"key": "value",
			},
			`<key>value</key>`,
		},
		{
			"array of objects",
			[]interface{}{
				map[string]interface{}{
					"key": "value",
				},
			},
			`<root><key>value</key></root>`,
		},
		{
			"string",
			"data",
			`<root>data</root>`,
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			serializer := xmlSerializer{
				rootTag: "root",
			}

			bytes, err := serializer.Serialize(test.data, "")

			assert.NoError(t, err)
			assert.Equal(t, test.expectedData, string(bytes))
		})
	}
}
