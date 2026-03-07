!pip install beautifulsoup4 pandas

import os
import pandas as pd
from bs4 import BeautifulSoup

# folder containing downloaded HTML files
folder_path = 'downloaded_courses'

# list to store extracted data
courses = []

for filename in os.listdir(folder_path):
    if filename.endswith(".html"):
        file_path = os.path.join(folder_path, filename)
        with open(file_path, 'r', encoding='utf-8') as f:
            soup = BeautifulSoup(f, 'html.parser')

            # You need to adjust these based on the HTML structure
            # Example placeholders:
            code = soup.find('span', class_='course-code')
            name = soup.find('h1', class_='course-name')
            about = soup.find('div', class_='course-about')

            # get text safely
            code_text = code.get_text(strip=True) if code else ''
            name_text = name.get_text(strip=True) if name else ''
            about_text = about.get_text(strip=True) if about else ''

            courses.append({
                'course code': code_text,
                'course name': name_text,
                'course about': about_text
            })

# create DataFrame and save CSV
df = pd.DataFrame(courses)
df.to_csv('courses.csv', index=False)
print("CSV created successfully!")
