import os
import sys
import asyncio
import re
from g4f.client import Client
import pdfplumber

sys.stdout.reconfigure(encoding='utf-8')
sys.stderr.reconfigure(encoding='utf-8')  # Đảm bảo stderr cũng dùng UTF-8
client = Client()

os.environ["G4F_NO_UPDATE"] = "true"  # Ẩn thông báo cập nhật
os.environ["G4F_DEBUG"] = "false"  # Ẩn debug logs

# Chặn in ra stdout và stderr của g4f
class HiddenPrints:
    def __enter__(self):
        self._original_stdout = sys.stdout
        self._original_stderr = sys.stderr
        sys.stdout = open(os.devnull, 'w')
        sys.stderr = open(os.devnull, 'w')

    def __exit__(self, exc_type, exc_val, exc_tb):
        sys.stdout.close()
        sys.stderr.close()
        sys.stdout = self._original_stdout
        sys.stderr = self._original_stderr

if len(sys.argv) < 2:
    question = input("Bạn: ")  # Cho phép nhập tay để kiểm tra
else:
    question = sys.argv[1]

# Đường dẫn tuyệt đối đến file PDF
pdf_file_path = "F:/xampp/htdocs/MAIN_php/MTvE.pdf"

def read_pdf(file_path):
    try:
        with pdfplumber.open(file_path) as pdf:
            text = ""
            for page in pdf.pages:
                extracted_text = page.extract_text()
                if extracted_text:
                    text += extracted_text + "\n"
        return text
    except Exception as e:
        return f"Lỗi đọc file PDF: {str(e)}"

def generate_response(question, pdf_text):
    try:
        context = pdf_text[:6000] if len(pdf_text) > 6000 else pdf_text
        prompt = f"Đây là một đoạn văn từ tài liệu: {context}\n\nCâu hỏi: {question}\nTrả lời:"

        with HiddenPrints():  # Chặn toàn bộ log từ g4f
            response = client.chat.completions.create(
                model="gpt-4",
                messages=[{"role": "user", "content": prompt}],
                stream=False
            )
        answer = response.choices[0].message.content.strip()
        answer = re.sub(r'\n+', '\n', answer)  # Xóa dòng trống
        return answer
    except Exception as e:
        return f"Đã xảy ra lỗi: {str(e)}"


pdf_text = read_pdf(pdf_file_path)
if "Lỗi đọc file PDF" in pdf_text:
    print(pdf_text)
    sys.exit(1)

answer = generate_response(question, pdf_text)
print(answer)
