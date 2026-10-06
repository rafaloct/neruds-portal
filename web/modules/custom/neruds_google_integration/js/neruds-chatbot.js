/**
 * NERUDS Chatbot UI - Vanilla JavaScript
 * Integrates with Vertex AI Gemini 1.5 Pro
 */

(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.nerudsChatbot = {
    attach(context) {
      once('neruds-chatbot-init', '.neruds-chatbot-widget', context).forEach(() => {
        const chatbot = new NerudsChatbot();
        chatbot.init();
      });
    },
  };

  class NerudsChatbot {
    constructor() {
      this.sessionId = null;
      this.messages = [
        {
          id: 'welcome',
          role: 'assistant',
          content:
            'Olá! Sou o assistente inteligente do NERUDS. Posso ajudá-lo com buscas de publicações, datasets e informações sobre pesquisa.',
          timestamp: new Date(),
        },
      ];
      this.loading = false;
      this.isOpen = false;
    }

    async init() {
      this.createUI();
      this.attachEventListeners();
      await this.initSession();
    }

    createUI() {
      const container = document.querySelector('.neruds-chatbot-widget') || document.body;

      // Toggle button
      this.toggleBtn = document.createElement('button');
      this.toggleBtn.className = 'neruds-chatbot__toggle';
      this.toggleBtn.setAttribute('aria-label', 'Abrir assistente inteligente');
      this.toggleBtn.setAttribute('title', 'Assistente inteligente');
      this.toggleBtn.innerHTML = `
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span class="neruds-chatbot__badge">1</span>
      `;

      // Chatbot window
      this.window = document.createElement('div');
      this.window.className = 'neruds-chatbot__window';
      this.window.setAttribute('role', 'dialog');
      this.window.setAttribute('aria-labelledby', 'chatbot-title');
      this.window.style.display = 'none';
      this.window.innerHTML = this.getWindowHTML();

      // Inject styles
      this.injectStyles();

      // Append to container
      container.appendChild(this.toggleBtn);
      container.appendChild(this.window);

      // Store references
      this.messagesContainer = this.window.querySelector('.neruds-chatbot__messages');
      this.input = this.window.querySelector('.neruds-chatbot__input');
      this.form = this.window.querySelector('.neruds-chatbot__form');
      this.sendBtn = this.window.querySelector('.neruds-chatbot__btn-send');

      // Render initial messages
      this.renderMessages();
    }

    getWindowHTML() {
      return `
        <div class="neruds-chatbot__header">
          <h2 id="chatbot-title" class="neruds-chatbot__title">Assistente NERUDS</h2>
          <div class="neruds-chatbot__actions">
            <button class="neruds-chatbot__btn-icon neruds-chatbot__btn-clear" title="Limpar conversa" aria-label="Limpar histórico">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              </svg>
            </button>
            <button class="neruds-chatbot__btn-icon neruds-chatbot__btn-close" title="Fechar" aria-label="Fechar assistente">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
        </div>
        <div class="neruds-chatbot__messages" role="log"></div>
        <div class="neruds-chatbot__error" style="display: none;" role="alert"></div>
        <form class="neruds-chatbot__form">
          <input type="text" class="neruds-chatbot__input" placeholder="Faça uma pergunta..." aria-label="Mensagem">
          <button type="submit" class="neruds-chatbot__btn-send" title="Enviar mensagem" aria-label="Enviar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
              <path d="M16.6915026,12.4744748 L3.50612381,13.2599618 C3.19218622,13.2599618 3.03521743,13.4170592 3.03521743,13.5741566 L1.15159189,20.0151496 C0.8376543,20.8006365 0.99,21.89 1.77946707,22.52 C2.41,22.99 3.50612381,23.1 4.13399899,22.8429026 L21.714504,14.0454487 C22.6563168,13.5741566 23.1272231,12.6315722 22.9702544,11.6889879 L4.13399899,1.16365967 C3.34915502,0.9 2.40734225,0.9 1.77946707,1.4433931 C0.994623095,2.10604706 0.837654326,3.0486314 1.15159189,3.99701575 L3.03521743,10.4380088 C3.03521743,10.5951061 3.19218622,10.7522035 3.50612381,10.7522035 L16.6915026,11.5376905 C16.6915026,11.5376905 17.1624089,11.5376905 17.1624089,12.0090827 C17.1624089,12.4744748 16.6915026,12.4744748 16.6915026,12.4744748 Z"/>
            </svg>
          </button>
        </form>
      `;
    }

    attachEventListeners() {
      // Toggle button
      this.toggleBtn.addEventListener('click', () => this.toggleWindow());

      // Close button
      this.window.querySelector('.neruds-chatbot__btn-close').addEventListener('click', () => {
        this.closeWindow();
      });

      // Clear button
      this.window.querySelector('.neruds-chatbot__btn-clear').addEventListener('click', () => {
        this.clearHistory();
      });

      // Form submission
      this.form.addEventListener('submit', (e) => this.handleSubmit(e));

      // Auto-focus input when window opens
      this.toggleBtn.addEventListener('click', () => {
        if (this.isOpen) {
          setTimeout(() => this.input.focus(), 100);
        }
      });
    }

    toggleWindow() {
      if (this.isOpen) {
        this.closeWindow();
      } else {
        this.openWindow();
      }
    }

    openWindow() {
      this.window.style.display = 'flex';
      this.isOpen = true;
      this.input.focus();
      this.scrollToBottom();
    }

    closeWindow() {
      this.window.style.display = 'none';
      this.isOpen = false;
    }

    async initSession() {
      try {
        const response = await fetch('/neruds/api/chat/session', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
        });
        const data = await response.json();
        this.sessionId = data.session_id;
        this.input.disabled = false;
      } catch (err) {
        console.error('Failed to initialize chat session:', err);
        this.showError('Falha ao inicializar sessão');
      }
    }

    async handleSubmit(e) {
      e.preventDefault();

      const message = this.input.value.trim();
      if (!message || this.loading || !this.sessionId) {
        return;
      }

      // Add user message
      const userMsg = {
        id: `msg-${Date.now()}`,
        role: 'user',
        content: message,
        timestamp: new Date(),
      };
      this.messages.push(userMsg);
      this.input.value = '';
      this.renderMessages();

      this.loading = true;
      this.sendBtn.disabled = true;
      this.showTypingIndicator();

      try {
        const response = await fetch('/neruds/api/chat', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            message: userMsg.content,
            session_id: this.sessionId,
            context: {
              page: window.location.pathname,
              timestamp: new Date().toISOString(),
            },
          }),
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();

        // Remove typing indicator
        const typingMsg = this.messagesContainer.querySelector('.neruds-chatbot__typing');
        if (typingMsg) {
          typingMsg.closest('.neruds-chatbot__message').remove();
        }

        // Add assistant message
        const assistantMsg = {
          id: `msg-${Date.now()}`,
          role: 'assistant',
          content: data.response,
          timestamp: new Date(),
          metadata: data.metadata || {},
        };
        this.messages.push(assistantMsg);
        this.renderMessages();
        this.hideError();
      } catch (err) {
        this.showError(`Erro: ${err.message}`);
        // Remove typing indicator
        const typingMsg = this.messagesContainer.querySelector('.neruds-chatbot__typing');
        if (typingMsg) {
          typingMsg.closest('.neruds-chatbot__message').remove();
        }
      } finally {
        this.loading = false;
        this.sendBtn.disabled = false;
        this.scrollToBottom();
      }
    }

    renderMessages() {
      this.messagesContainer.innerHTML = '';

      this.messages.forEach((msg) => {
        const msgEl = document.createElement('div');
        msgEl.className = `neruds-chatbot__message neruds-chatbot__message--${msg.role}`;
        msgEl.innerHTML = `
          <div class="neruds-chatbot__message-avatar">
            <span class="neruds-chatbot__avatar-icon">${msg.role === 'user' ? '👤' : '🤖'}</span>
          </div>
          <div class="neruds-chatbot__message-content">
            <p class="neruds-chatbot__message-text">${this.escapeHtml(msg.content)}</p>
            <time class="neruds-chatbot__message-time">
              ${msg.timestamp.toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit',
              })}
            </time>
          </div>
        `;
        this.messagesContainer.appendChild(msgEl);
      });
    }

    showTypingIndicator() {
      const msgEl = document.createElement('div');
      msgEl.className = 'neruds-chatbot__message neruds-chatbot__message--assistant';
      msgEl.innerHTML = `
        <div class="neruds-chatbot__message-avatar">
          <span class="neruds-chatbot__avatar-icon">🤖</span>
        </div>
        <div class="neruds-chatbot__message-content">
          <div class="neruds-chatbot__typing">
            <span></span>
            <span></span>
            <span></span>
          </div>
        </div>
      `;
      this.messagesContainer.appendChild(msgEl);
      this.scrollToBottom();
    }

    showError(message) {
      const errorEl = this.window.querySelector('.neruds-chatbot__error');
      errorEl.textContent = message;
      errorEl.style.display = 'block';
    }

    hideError() {
      const errorEl = this.window.querySelector('.neruds-chatbot__error');
      errorEl.style.display = 'none';
    }

    clearHistory() {
      if (confirm('Limpar histórico da conversa?')) {
        this.messages = [this.messages[0]]; // Keep welcome
        this.renderMessages();
      }
    }

    scrollToBottom() {
      this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
    }

    escapeHtml(text) {
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
      };
      return text.replace(/[&<>"']/g, (m) => map[m]);
    }

    injectStyles() {
      if (document.querySelector('#neruds-chatbot-styles')) {
        return;
      }

      const style = document.createElement('style');
      style.id = 'neruds-chatbot-styles';
      style.textContent = this.getStyles();
      document.head.appendChild(style);
    }

    getStyles() {
      return `
        .neruds-chatbot__toggle {
          position: fixed;
          bottom: 24px;
          right: 24px;
          width: 56px;
          height: 56px;
          border-radius: 50%;
          background: #3498db;
          color: white;
          border: none;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
          transition: all 0.3s ease;
          z-index: 999;
        }

        .neruds-chatbot__toggle:hover {
          background: #2980b9;
          box-shadow: 0 6px 16px rgba(52, 152, 219, 0.4);
          transform: scale(1.05);
        }

        .neruds-chatbot__toggle:active {
          transform: scale(0.95);
        }

        .neruds-chatbot__badge {
          position: absolute;
          top: -4px;
          right: -4px;
          background: #e74c3c;
          color: white;
          border-radius: 50%;
          width: 24px;
          height: 24px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 12px;
          font-weight: bold;
        }

        .neruds-chatbot__window {
          position: fixed;
          bottom: 88px;
          right: 24px;
          width: 400px;
          max-width: calc(100vw - 48px);
          height: 600px;
          border-radius: 12px;
          background: white;
          box-shadow: 0 5px 40px rgba(0, 0, 0, 0.16);
          flex-direction: column;
          z-index: 999;
          animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
          from {
            opacity: 0;
            transform: translateY(20px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }

        .neruds-chatbot__header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 16px;
          border-bottom: 1px solid #e0e0e0;
          background: #f9f9f9;
          border-radius: 12px 12px 0 0;
        }

        .neruds-chatbot__title {
          margin: 0;
          font-size: 16px;
          font-weight: 600;
          color: #2c3e50;
        }

        .neruds-chatbot__actions {
          display: flex;
          gap: 8px;
        }

        .neruds-chatbot__btn-icon {
          background: none;
          border: none;
          cursor: pointer;
          color: #7f8c8d;
          padding: 4px;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 4px;
          transition: all 0.2s;
        }

        .neruds-chatbot__btn-icon:hover {
          background: #e8e8e8;
          color: #2c3e50;
        }

        .neruds-chatbot__messages {
          flex: 1;
          overflow-y: auto;
          padding: 16px;
          display: flex;
          flex-direction: column;
          gap: 12px;
        }

        .neruds-chatbot__message {
          display: flex;
          gap: 8px;
          animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
          from {
            opacity: 0;
            transform: translateY(8px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }

        .neruds-chatbot__message--user {
          justify-content: flex-end;
        }

        .neruds-chatbot__message--user .neruds-chatbot__message-content {
          background: #3498db;
          color: white;
        }

        .neruds-chatbot__message--assistant .neruds-chatbot__message-content {
          background: #f0f0f0;
          color: #2c3e50;
        }

        .neruds-chatbot__message-avatar {
          flex-shrink: 0;
          width: 32px;
          height: 32px;
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .neruds-chatbot__avatar-icon {
          font-size: 20px;
        }

        .neruds-chatbot__message-content {
          max-width: 70%;
          padding: 10px 12px;
          border-radius: 8px;
          word-break: break-word;
        }

        .neruds-chatbot__message-text {
          margin: 0 0 4px 0;
          font-size: 14px;
          line-height: 1.4;
        }

        .neruds-chatbot__message-time {
          font-size: 12px;
          opacity: 0.7;
          display: block;
        }

        .neruds-chatbot__typing {
          display: flex;
          gap: 4px;
          padding: 8px 0;
        }

        .neruds-chatbot__typing span {
          width: 8px;
          height: 8px;
          border-radius: 50%;
          background: #7f8c8d;
          animation: typing 1.4s infinite;
        }

        .neruds-chatbot__typing span:nth-child(2) {
          animation-delay: 0.2s;
        }

        .neruds-chatbot__typing span:nth-child(3) {
          animation-delay: 0.4s;
        }

        @keyframes typing {
          0%, 60%, 100% {
            opacity: 0.5;
            transform: translateY(0);
          }
          30% {
            opacity: 1;
            transform: translateY(-10px);
          }
        }

        .neruds-chatbot__error {
          background: #fadbd8;
          border: 1px solid #f1948a;
          color: #c0392b;
          padding: 12px 16px;
          font-size: 13px;
        }

        .neruds-chatbot__form {
          display: flex;
          gap: 8px;
          padding: 12px 16px;
          border-top: 1px solid #e0e0e0;
          background: white;
          border-radius: 0 0 12px 12px;
        }

        .neruds-chatbot__input {
          flex: 1;
          border: 1px solid #e0e0e0;
          border-radius: 6px;
          padding: 10px 12px;
          font-size: 14px;
          font-family: inherit;
          transition: border-color 0.2s;
        }

        .neruds-chatbot__input:focus {
          outline: none;
          border-color: #3498db;
          box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .neruds-chatbot__input:disabled {
          background: #f9f9f9;
          color: #7f8c8d;
        }

        .neruds-chatbot__btn-send {
          background: #3498db;
          color: white;
          border: none;
          border-radius: 6px;
          padding: 10px 12px;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          transition: all 0.2s;
          flex-shrink: 0;
        }

        .neruds-chatbot__btn-send:hover:not(:disabled) {
          background: #2980b9;
        }

        .neruds-chatbot__btn-send:disabled {
          background: #bdc3c7;
          cursor: not-allowed;
        }

        @media (max-width: 768px) {
          .neruds-chatbot__window {
            position: fixed;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 100%;
            max-width: 100%;
            border-radius: 0;
          }

          .neruds-chatbot__message-content {
            max-width: 85%;
          }

          .neruds-chatbot__messages {
            padding: 12px;
          }
        }

        .neruds-chatbot__messages::-webkit-scrollbar {
          width: 6px;
        }

        .neruds-chatbot__messages::-webkit-scrollbar-track {
          background: transparent;
        }

        .neruds-chatbot__messages::-webkit-scrollbar-thumb {
          background: #d0d0d0;
          border-radius: 3px;
        }

        .neruds-chatbot__messages::-webkit-scrollbar-thumb:hover {
          background: #bbb;
        }
      `;
    }
  }
})(Drupal, once);
