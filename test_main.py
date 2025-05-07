import asyncio
from asyncio import WindowsSelectorEventLoopPolicy
from g4f.client import Client
import pdfplumber


# Thiết lập vòng lặp sự kiện cho Windows
asyncio.set_event_loop_policy(WindowsSelectorEventLoopPolicy())

# Khởi tạo client của g4f
client = Client()

# Hàm đọc nội dung từ file PDF
def read_pdf(file_path):
    with pdfplumber.open(file_path) as pdf:
        text = ""
        for page in pdf.pages:
            text += page.extract_text() + "\n"
    return text

# Hàm trả lời câu hỏi dựa trên nội dung PDF
def generate_response(question, pdf_text):
    try:
        # Giới hạn ngữ cảnh nếu văn bản quá dài
        context = pdf_text[:6000] if len(pdf_text) > 6000 else pdf_text

        # Thiết lập prompt với câu hỏi và ngữ cảnh
        prompt = f"Đây là một đoạn văn từ tài liệu: {context}\n\nCâu hỏi: {question}\nTrả lời:"

        # Sử dụng g4f.client để gọi mô hình OpenAI
        response = client.chat.completions.create(
            model="gpt-4o-mini",  # Chọn mô hình
            messages=[{"role": "user", "content": prompt}],
        )

        # Trích xuất câu trả lời từ kết quả trả về
        answer = response.choices[0].message.content
        return answer
    except Exception as e:
        return f"Đã xảy ra lỗi: {str(e)}"

# Đọc nội dung từ file PDF
pdf_file_path = 'DATAA.pdf'  # Thay bằng đường dẫn đến file PDF của bạn
pdf_text = read_pdf(pdf_file_path)


if __name__ == "__main__":
    while True:
        question = input("Bạn: ")
        if question.lower() in ["exit", "quit"]:
            break
        answer = generate_response(question, pdf_text)
        print("Chatbot:", answer)