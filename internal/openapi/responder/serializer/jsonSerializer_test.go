package serializer

import (
	"github.com/stretchr/testify/assert"
	"math"
	"testing"
)

func TestJsonSerializer_Serialize_SerializableData_SerializedData(t *testing.T) {
	serializer := jsonSerializer{}
	data := map[string]string{
		"key": "value",
	}

	bytes, err := serializer.Serialize(data, "")

	assert.NoError(t, err)
	assert.Equal(t, `{"key":"value"}`, string(bytes))
}

func TestJsonSerializer_Serialize_UnserializableData_Error(t *testing.T) {
	serializer := jsonSerializer{}

	bytes, err := serializer.Serialize(math.Inf(1), "")

	assert.EqualError(t, err, "json: unsupported value: +Inf")
	assert.Nil(t, bytes)
}
