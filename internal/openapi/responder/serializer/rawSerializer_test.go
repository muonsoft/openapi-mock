package serializer

import (
	"github.com/stretchr/testify/assert"
	"testing"
)

type stringable struct {
	data string
}

func (s stringable) String() string {
	return s.data
}

func TestRawSerializer_Serialize_SerializableData_BytesReturned(t *testing.T) {
	tests := []struct {
		name          string
		data          interface{}
		expectedBytes []byte
	}{
		{
			"bytes",
			[]byte("data"),
			[]byte("data"),
		},
		{
			"string",
			"data",
			[]byte("data"),
		},
		{
			"stringable",
			&stringable{data: "data"},
			[]byte("data"),
		},
	}
	for _, test := range tests {
		t.Run(test.name, func(t *testing.T) {
			serializer := rawSerializer{}

			data, err := serializer.Serialize(test.data, "")

			assert.NoError(t, err)
			assert.Equal(t, test.expectedBytes, data)
		})
	}
}

func TestRawSerializer_Serialize_UnserializableData_Error(t *testing.T) {
	serializer := rawSerializer{}
	data := struct {
		data string
	}{data: "data"}

	serializedData, err := serializer.Serialize(data, "")

	assert.EqualError(t, err, "unserializable data")
	assert.Nil(t, serializedData)
}
