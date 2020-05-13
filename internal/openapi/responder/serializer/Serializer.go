package serializer

type Serializer interface {
	Serialize(data interface{}, format string) ([]byte, error)
}

func New() Serializer {
	return &coordinatingSerializer{
		formatSerializers: map[string]Serializer{
			"raw":  &rawSerializer{},
			"json": &jsonSerializer{},
			"xml":  &xmlSerializer{rootTag: "root"},
		},
	}
}
