import sys
import json
import time
from youtube_transcript_api import YouTubeTranscriptApi

# Reconfigure stdout to use UTF-8 to prevent encoding errors on Windows
if hasattr(sys.stdout, 'reconfigure'):
    sys.stdout.reconfigure(encoding='utf-8')

def format_time(seconds):
    hours = int(seconds // 3600)
    minutes = int((seconds % 3600) // 60)
    secs = int(seconds % 60)
    if hours > 0:
        return f"[{hours:02d}:{minutes:02d}:{secs:02d}]"
    else:
        return f"[{minutes:02d}:{secs:02d}]"

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No video ID provided"}))
        sys.exit(1)
        
    video_id = sys.argv[1]
    
    max_retries = 3
    delay_seconds = 2
    
    for attempt in range(max_retries):
        try:
            # Handle list methods dynamically for different versions of the library
            if hasattr(YouTubeTranscriptApi, 'list_transcripts'):
                transcript_list = YouTubeTranscriptApi.list_transcripts(video_id)
            else:
                api = YouTubeTranscriptApi()
                transcript_list = api.list(video_id)
            
            try:
                # Try to get Hindi or English
                transcript_obj = transcript_list.find_transcript(['hi', 'en'])
            except Exception:
                # Fallback to the first available transcript
                transcript_obj = next(iter(transcript_list))
                
            transcript = transcript_obj.fetch()
            
            formatted_transcript = []
            for entry in transcript:
                # Handle both dict (newer official library) and object (local fork library) formats
                if isinstance(entry, dict):
                    start = entry.get('start', 0)
                    duration = entry.get('duration', 0)
                    text = entry.get('text', '')
                else:
                    start = getattr(entry, 'start', 0)
                    duration = getattr(entry, 'duration', 0)
                    text = getattr(entry, 'text', '')
                
                formatted_transcript.append({
                    "start": start,
                    "duration": duration,
                    "text": text,
                    "time_str": format_time(start)
                })
                
            print(json.dumps(formatted_transcript, ensure_ascii=True))
            return # Successful execution
            
        except Exception as e:
            if attempt < max_retries - 1:
                # Wait and retry
                time.sleep(delay_seconds)
            else:
                # Final attempt failed
                print(json.dumps({"error": f"{type(e).__name__}: {str(e)}"}))
                sys.exit(1)

if __name__ == "__main__":
    main()
