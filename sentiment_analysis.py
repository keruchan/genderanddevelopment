from textblob import TextBlob
import sys
import json

def get_sentiment(text):
    # Perform sentiment analysis using TextBlob
    blob = TextBlob(text)
    polarity = blob.sentiment.polarity
    
    # Classify polarity as positive, negative, or neutral
    if polarity > 0:
        return 'positive'
    elif polarity < 0:
        return 'negative'
    else:
        return 'neutral'

if __name__ == "__main__":
    try:
        # Get the comment text from the command line argument passed by PHP
        if len(sys.argv) < 2:
            raise ValueError("No input text provided")
        
        comment_text = sys.argv[1]
        
        # Log the input for debugging
        with open("debug_log.txt", "a") as log_file:
            log_file.write(f"Input: {comment_text}\n")
        
        sentiment = get_sentiment(comment_text)
        
        # Output sentiment as JSON (for PHP to parse)
        print(json.dumps({"sentiment": sentiment}))
    
    except Exception as e:
        # Handle unexpected errors and log them
        with open("debug_log.txt", "a") as log_file:
            log_file.write(f"Error: {str(e)}\n")
        
        print(json.dumps({"sentiment": "neutral", "error": str(e)}))