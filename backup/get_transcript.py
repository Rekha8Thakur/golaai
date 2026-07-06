import sys
import os
from youtube_transcript_api import YouTubeTranscriptApi

def log(msg):
    print(msg)
    with open("script_log.txt", "a", encoding="utf-8") as f:
        f.write(msg + "\n")

def main():
    if os.path.exists("script_log.txt"):
        os.remove("script_log.txt")
        
    video_id = "o2-kgrUVUXc"
    log(f"Starting transcript extraction for video ID: {video_id}")
    try:
        log("Initializing YouTubeTranscriptApi...")
        api = YouTubeTranscriptApi()
        
        log("Retrieving transcript list...")
        transcript_list = api.list(video_id)
        log("Transcript list retrieved. Available transcripts:")
        for t in transcript_list:
            log(f" - {t.language_code} ({t.language}), generated={t.is_generated}")
            
        log("Finding Hindi transcript...")
        hi_transcript = transcript_list.find_transcript(['hi'])
        
        log("Fetching transcript text...")
        transcript = hi_transcript.fetch()
        log(f"Fetched {len(transcript)} segments.")
        
        # Save transcript to file with timestamps
        log("Saving to transcript.txt...")
        with open("transcript.txt", "w", encoding="utf-8") as f:
            for entry in transcript:
                start = entry.start
                hours = int(start // 3600)
                minutes = int((start % 3600) // 60)
                seconds = int(start % 60)
                time_str = f"[{hours:02d}:{minutes:02d}:{seconds:02d}]"
                f.write(f"{time_str} {entry.text}\n")
                
        log("Transcript saved successfully to transcript.txt")
    except Exception as e:
        log(f"Error fetching transcript: {type(e).__name__}: {e}")

if __name__ == "__main__":
    main()
