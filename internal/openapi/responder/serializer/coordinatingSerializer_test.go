package serializer

import (
	"errors"
	"github.com/stretchr/testify/assert"
	"testing"
)

func TestCoordinatingSerializer_Serialize_SupportedFormat_DataSerializedByFormatSerializer(t *testing.T) {
	formatSerializer := &MockSerializer{}
	serializer := &coordinatingSerializer{
		formatSerializers: map[string]Serializer{
			"format": formatSerializer,
		},
	}
	formatSerializer.On("Serialize", "data", "format").Return([]byte("serialized"), nil).Once()

	bytes, err := serializer.Serialize("data", "format")

	formatSerializer.AssertExpectations(t)
	assert.NoError(t, err)
	assert.Equal(t, "serialized", string(bytes))
}

func TestCoordinatingSerializer_Serialize_UnsupportedFormat_Error(t *testing.T) {
	serializer := &coordinatingSerializer{
		formatSerializers: map[string]Serializer{},
	}

	bytes, err := serializer.Serialize("data", "format")

	assert.EqualError(t, err, "serialization format 'format' is not supported")
	var unsupportedFormatError *UnsupportedFormatError
	assert.True(t, errors.As(err, &unsupportedFormatError))
	assert.Len(t, bytes, 0)
}
