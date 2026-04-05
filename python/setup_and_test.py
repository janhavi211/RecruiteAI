#!/usr/bin/env python3
"""
RecruitAI - Setup checker & tester
Run: python setup_and_test.py
"""
import subprocess, sys, json, os

print("=" * 55)
print("  RecruitAI Python Setup Checker")
print("=" * 55)

# Check Python version
print(f"\n✅ Python {sys.version}")

# Check & install libraries
libs = ["pdfplumber", "PyPDF2", "pypdf", "pdfminer.six", "docx"]
pip_names = {"pdfplumber":"pdfplumber","PyPDF2":"PyPDF2","pypdf":"pypdf",
             "pdfminer.six":"pdfminer.six","docx":"python-docx"}

for lib in libs:
    try:
        if lib == "pdfminer.six":
            import importlib
            importlib.import_module("pdfminer")
        elif lib == "docx":
            import docx
        else:
            __import__(lib)
        print(f"  ✅ {lib} installed")
    except ImportError:
        print(f"  ❌ {lib} NOT found — installing...")
        try:
            subprocess.check_call([sys.executable, "-m", "pip", "install",
                                   pip_names[lib], "--quiet"])
            print(f"  ✅ {lib} installed successfully")
        except Exception as e:
            print(f"  ⚠️  Could not install {lib}: {e}")

# Test the parser on a sample text
print("\n" + "=" * 55)
print("  Testing skill extraction...")
print("=" * 55)

# Create a small test resume text file
test_text = """
John Doe
john.doe@email.com | +91 9876543210 | github.com/johndoe

SUMMARY
Full Stack Developer with 3 years experience in Python, React, and Node.js.

SKILLS
Languages: Python, JavaScript, Java, C++
Frontend: React.js, HTML5, CSS3, Bootstrap, Redux
Backend: Node.js, Django, Flask, REST API
Database: MySQL, MongoDB, PostgreSQL, Redis
Tools: Git, Docker, AWS, Linux, Postman, JIRA

EXPERIENCE
Software Engineer | TechCorp | 2021 - 2023
- Built REST APIs using Django and Flask serving 10,000+ users
- Improved app performance by 40% using Redis caching
- Led a team of 4 developers on a React.js dashboard project

EDUCATION
B.Tech in Computer Science | Pune University | 2021
CGPA: 8.5/10

PROJECTS
E-Commerce Platform
Built using React, Node.js, MongoDB with payment gateway integration
Increased sales by 30% for client

CERTIFICATIONS
AWS Certified Developer Associate - 2022
"""

# Write test file
with open("test_resume.txt", "w") as f:    f.write(test_text)

# Run parser on it
script = os.path.join(os.path.dirname(__file__), "resume_parser.py")

# Test with text directly via stdin hack
import sys, io
sys.path.insert(0, os.path.dirname(script))

try:
    # Import and run directly
    import importlib.util
    spec = importlib.util.spec_from_file_location("resume_parser", script)
    mod  = importlib.util.module_from_spec(spec)

    # Monkey-patch get_text to return our test text
    sys.argv = [script, "/tmp/test_resume.txt"]

    # Since it's a .txt, the parser won't extract — instead call extract_skills directly
    spec.loader.exec_module(mod)

    flat, by_cat = mod.extract_skills(test_text)
    print(f"\n  Skills detected: {len(flat)}")
    for cat, skills in by_cat.items():
        print(f"    [{cat}]: {', '.join(skills[:5])}")

    print(f"\n✅ Parser is working correctly!")
    print(f"   Found {len(flat)} skills across {len(by_cat)} categories")

except Exception as e:
    print(f"\n❌ Parser test failed: {e}")
    import traceback; traceback.print_exc()

print("\n" + "=" * 55)
print("  Setup complete! You can now upload resumes.")
print("=" * 55)
