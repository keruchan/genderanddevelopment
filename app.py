from flask import Flask, render_template, request, jsonify
from textblob import TextBlob
import mysql.connector
import json

app = Flask(__name__)

# Database connection details (you can update with your actual credentials)
db_config = {
    "host": "localhost",  # change to your MySQL host
    "user": "root",       # change to your MySQL username
    "password": "",       # change to your MySQL password
    "database": "gad"  # change to your database name
}

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

# Database connection
def get_db_connection():
    conn = mysql.connector.connect(**db_config)
    return conn

# Home page route to display event comments and analysis
@app.route('/event_comments')
def event_comments():
    event_id = request.args.get('event_id', type=int, default=0)
    page = request.args.get('page', type=int, default=1)
    comments_per_page = 10
    offset = (page - 1) * comments_per_page

    if event_id == 0:
        return "Event not found", 404
    
    # Fetch event title
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT title FROM events WHERE id = %s", (event_id,))
    event_title = cursor.fetchone()
    if not event_title:
        return "Event not found", 404
    event_title = event_title[0]

    # Fetch comments count
    cursor.execute("SELECT COUNT(*) FROM event_evaluations WHERE event_id = %s", (event_id,))
    total_comments = cursor.fetchone()[0]

    # Calculate the total number of pages
    total_pages = (total_comments // comments_per_page) + (1 if total_comments % comments_per_page else 0)

    # Fetch comments for the specific event
    cursor.execute("""
        SELECT ee.comments, ee.created_at, u.firstname, u.lastname 
        FROM event_evaluations ee 
        JOIN users u ON ee.user_id = u.id 
        WHERE ee.event_id = %s 
        ORDER BY ee.created_at DESC
        LIMIT %s OFFSET %s
    """, (event_id, comments_per_page, offset))
    
    comments_list = []
    sentiment_counts = {'positive': 0, 'negative': 0, 'neutral': 0}
    
    for (comments, created_at, firstname, lastname) in cursor.fetchall():
        sentiment = get_sentiment(comments)
        sentiment_counts[sentiment] += 1
        
        comments_list.append({
            'comments': comments,
            'created_at': created_at,
            'firstname': firstname,
            'lastname': lastname,
            'sentiment': sentiment
        })
    
    cursor.close()
    conn.close()
    
    return render_template('event_comments.html', event_title=event_title, comments=comments_list, sentiment_counts=sentiment_counts, total_pages=total_pages, page=page, event_id=event_id)

if __name__ == "__main__":
    app.run(debug=True)
