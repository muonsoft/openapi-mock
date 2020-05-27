package serializer

type coordinatingSerializer struct {
	formatSerializers map[string]Serializer
}

func (serializer *coordinatingSerializer) Serialize(data interface{}, format string) ([]byte, error) {
	formatSerializer, isSupported := serializer.formatSerializers[format]
	if !isSupported {
		return nil, &UnsupportedFormatError{Format: format}
	}

	return formatSerializer.Serialize(data, format)
}
