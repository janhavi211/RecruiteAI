import sys
import json
import pdfplumber

# =========================
# 1. GET FILE PATH
# =========================
file = sys.argv[1]

# =========================
# 2. EXTRACT TEXT
# =========================
text = ""

try:
    with pdfplumber.open(file) as pdf:
        for page in pdf.pages:
            text += page.extract_text() or ""
except:
    text = ""

text_lower = text.lower()

# =========================
# 3. RULE-BASED SKILLS DB
# =========================
skills_db = {
    "python": 10,
    "java": 8,
    "javascript": 8,
    "html": 5,
    "css": 5,
    "mysql": 7,
    "firebase": 7,
    "android": 9,
    "react": 9,
    "php": 7,
    "api": 6,
    "machine learning": 12,
    "data science": 12
}

skills = []
score = 0

# =========================
# 4. SKILL MATCHING
# =========================
for skill, val in skills_db.items():
    if skill in text_lower:
        skills.append(skill)
        score += val

# =========================
# 5. EXPERIENCE RULE
# =========================
experience = []

if "intern" in text_lower:
    experience.append("Internship")
    score += 10

if "project" in text_lower:
    experience.append("Project Work")
    score += 5

# =========================
# 6. EDUCATION RULE
# =========================
education = []

if "b.tech" in text_lower or "btech" in text_lower:
    education.append("B.Tech")
    score += 15

elif "diploma" in text_lower:
    education.append("Diploma")
    score += 10

elif "12th" in text_lower:
    education.append("12th")
    score += 5

# =========================
# 7. PROJECTS
# =========================
projects = []

if "project" in text_lower:
    projects.append("Project found")

# =========================
# 8. CERTIFICATIONS
# =========================
certifications = []

if "certificate" in text_lower or "certification" in text_lower:
    certifications.append("Certification found")
    score += 5

# =========================
# 9. ACHIEVEMENTS
# =========================
achievements = []

if "award" in text_lower or "rank" in text_lower:
    achievements.append("Achievement found")
    score += 5

# =========================
# 10. LANGUAGES
# =========================
languages = []

if "english" in text_lower:
    languages.append("English")

# =========================
# 11. FINAL OUTPUT
# =========================
result = {
    "skills": skills,
    "experience": experience,
    "education": education,
    "projects": projects,
    "certifications": certifications,
    "achievements": achievements,
    "languages": languages,
    "score": score,
    "full_text": text[:2000]  # limit text
}

print(json.dumps(result))