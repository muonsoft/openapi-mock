package config

func defaultOnNilUint16(v *uint16, defaultValue uint16) uint16 {
	if v == nil {
		return defaultValue
	}

	return *v
}

func defaultOnNilInt64(v *int64, defaultValue int64) int64 {
	if v == nil {
		return defaultValue
	}

	return *v
}

func defaultOnNilFloat(v *float64, defaultValue float64) float64 {
	if v == nil {
		return defaultValue
	}

	return *v
}

func defaultOnEmptyString(s string, defaultValue string) string {
	if s == "" {
		return defaultValue
	}

	return s
}
