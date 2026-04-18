# Programimi-me-Sockets-TCP

📡 Rrjetat Kompjuterike – Projekti 2
TCP Server & Client në C
📌 Përshkrimi i Projektit

Ky projekt implementon një server TCP dhe klientë në gjuhën C, të cilët komunikojnë në një rrjet real. Serveri menaxhon lidhjet e shumë klientëve, përpunon kërkesat e tyre dhe ruan mesazhet për monitorim. Gjithashtu, serveri përfshin një HTTP server të thjeshtë për monitorimin e statistikave në kohë reale.

⚙️ Teknologjitë e përdorura
Gjuha programuese: C
Protokolli: TCP
Socket Programming (POSIX)
HTTP (server i thjeshtë manual)
🖥️ Funksionalitetet e Serverit
1. Konfigurimi
Serveri përdor:
IP adresë: 0.0.0.0
Port TCP: 9000
Port HTTP: 8080
2. Menaxhimi i klientëve
Pranon lidhje nga shumë klientë (min. 4)
Ka limit të klientëve (p.sh. 6)
Nëse tejkalohet limiti:
Refuzon lidhjet e reja
3. Trajtimi i kërkesave
Serveri lexon mesazhe nga klientët
I ruan mesazhet në një strukturë (log)
Secili klient mund të dërgojë kërkesa
4. Timeout dhe rikuperim
Nëse klienti nuk dërgon mesazh për një kohë:
lidhja mbyllet automatikisht
Klienti mund të lidhet përsëri pa problem
5. Qasje në file (Admin)

Një klient ka privilegje të plota:

read
write
execute
6. HTTP Server (monitorim)

Serveri HTTP punon paralelisht në portin 8080.

Endpoint:
GET /stats
Kthen:
numrin e klientëve aktivë
IP adresat
numrin e mesazheve
listën e mesazheve
Format:
JSON ose tekst
💻 Funksionalitetet e Klientit
1. Lidhja me server
Klienti lidhet përmes:
IP adresës
portit të serverit
2. Dërgimi dhe marrja e mesazheve
Dërgon mesazhe tekstuale
Merr përgjigje nga serveri
3. Llojet e klientëve
🔹 Admin (full access)

Ka këto komanda:

Komanda	Përshkrimi
/list	Liston file-t
/read	Lexon file
/upload	Ngarkon file
/download	Shkarkon file
/delete	Fshin file
/search	Kërkon file
/info	Info për file
🔹 Klient normal
Ka vetëm read permission
Nuk mund të modifikojë file
4. Prioriteti
Klientët admin kanë:
përgjigje më të shpejtë
▶️ Udhëzime për Ekzekutim
1. Kompilimi
Server:
gcc server.c -o server
Client:
gcc client.c -o client
2. Ekzekutimi
Starto serverin:
./server
Starto klientin:
./client
3. Testimi me shumë klientë
Hap 4 terminale ose pajisje të ndryshme
Lidhu me serverin nga secili
4. Testimi i HTTP serverit

Në browser:

http://localhost:8080/stats
📊 Shembuj Ekzekutimi
Shembull 1 – Mesazh
Client: Hello Server
Server: Message received
Shembull 2 – Komanda /list
Client: /list
Server:
file1.txt
file2.txt
Shembull 3 – HTTP /stats
{
  "clients": 3,
  "messages": 10,
  "ips": ["192.168.0.2", "192.168.0.3"]
}
📁 Struktura e Projektit
project/
│
├── server.c
├── client.c
├── files/          # file të serverit
├── README.md
👥 Kontributi
Secili anëtar:
ka kontribuar në kod
ka testuar klientin dhe serverin
ka bërë commit në GitHub
⚠️ Vërejtje
Projekti duhet të ekzekutohet në rrjet real
Minimumi 4 klientë aktivë
Repository duhet të jetë publik
Commit-et duhet të jenë të vazhdueshme
✅ Përfundim

Ky projekt demonstron:

komunikim në rrjet me TCP
menaxhim të shumë klientëve
kontroll të aksesit
implementim të një HTTP serveri për monitorim