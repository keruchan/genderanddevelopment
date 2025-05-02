from flask import Flask, render_template, request, jsonify
from textblob import TextBlob
import json

app = Flask(__name__)

# Function to analyze sentiment using TextBlob
def get_sentiment(text):
    blob = TextBlob(text)
    polarity = blob.sentiment.polarity
    
    if polarity > 0:
        return 'positive'
    elif polarity < 0:
        return 'negative'
    else:
        return 'neutral'

# Sample data for the event and comments
event_data = {
    "id": 1,
    "title": "Gender and Development Workshop",
    "comments": [
        {"comment": "This workshop gave a great overview of gender issues.", "created_at": "2025-05-01 10:00:00", "firstname": "John", "lastname": "Doe"},
        {"comment": "Really insightful discussion on cultural barriers.", "created_at": "2025-05-01 11:30:00", "firstname": "Jane", "lastname": "Smith"},
        {"comment": "I didn't find the material very helpful.", "created_at": "2025-05-01 12:00:00", "firstname": "Alice", "lastname": "Johnson"},
        {"comment": "Excellent speakers and well-structured.", "created_at": "2025-05-01 13:15:00", "firstname": "Bob", "lastname": "Davis"}
    ]
}

@app.route('/')
def index():
    comments_list = []
    sentiment_counts = {'positive': 0, 'negative': 0, 'neutral': 0}
    
    # Analyze sentiment for each comment
    for comment in event_data["comments"]:
        sentiment = get_sentiment(comment["comment"])
        sentiment_counts[sentiment] += 1
        
        comments_list.append({
            'comment': comment["comment"],
            'created_at': comment["created_at"],
            'firstname': comment["firstname"],
            'lastname': comment["lastname"],
            'sentiment': sentiment
        })
    
    return render_template('event_comments.html', event_title=event_data["title"], comments=comments_list, sentiment_counts=sentiment_counts)

@app.route('/get_sentiment_data', methods=['GET'])
def get_sentiment_data():
    sentiment_counts = {'positive': 0, 'negative': 0, 'neutral': 0}
    
    # Analyze sentiment for each comment
    for comment in event_data["comments"]:
        sentiment = get_sentiment(comment["comment"])
        sentiment_counts[sentiment] += 1
    
    return jsonify(sentiment_counts)

if __name__ == "__main__":
    app.run(debug=True)
