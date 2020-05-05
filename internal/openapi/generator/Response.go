package generator

type Response struct {
	StatusCode int
	MediaType  string
	Data       map[string]interface{}
}
