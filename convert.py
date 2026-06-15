import pypandoc
import os

md_file = 'PROJECT_REBUILD_COURSE.md'
docx_file = 'PROJECT_REBUILD_COURSE.docx'

if os.path.exists(md_file):
    print("Converting Markdown to DOCX...")
    output = pypandoc.convert_file(md_file, 'docx', outputfile=docx_file)
    print("Conversion complete!")
else:
    print("Markdown file not found!")
