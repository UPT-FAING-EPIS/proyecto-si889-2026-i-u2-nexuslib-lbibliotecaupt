---
marp: true
theme: gaia
_class: lead
paginate: true
color: #f0faf9
style: |
  section {
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
    padding: 55px 70px;
    background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    font-size: 30px; text-decoration: none;
  }
  section.lead {
    justify-content: center;
    text-align: center;
  }
  h1 {
    color: #ffffff;
  }
  h2 {
    color: #ffffff;
    border-bottom: 3px solid #4db6ac;
    padding-bottom: 12px;
    margin-bottom: 40px;
  }
  ul, ol {
    margin-top: 15px;
  }
  li {
    margin-bottom: 25px;
    line-height: 1.6;
  }
---


# 🎓 Plataforma NexusLib
### Buscador Unificado de Recursos para Bibliotecas Físicas y Virtuales 
Universidad Privada de Tacna – 2026  

---

**Integrantes:**
- Hurtado Ortiz, Leandro Diego  
- Flores Navarro, Eduardo Gino  
- Cortez Mamani, Julio Samuel  

**Curso:** Patrones de Software  
**Docente:** Ing. Patrick Cuadros Quiroga  

---

## ❗ Problema

Actualmente, los estudiantes de la UPT lidian con un acceso fragmentado en la intranet de la biblioteca.

El uso de la plataforma actual genera:
- Desconexión total entre el catálogo físico y los portales virtuales  
- Redirecciones obligatorias a páginas externas de proveedores  
- Triplicación del tiempo y esfuerzo al realizar búsquedas aisladas  

---

## 💡 Solución

Se propone el desarrollo de un meta-buscador híbrido unificado:

Un sistema que permite a los estudiantes:
- Consultar múltiples repositorios de manera simultánea en un solo lugar  
- Visualizar existencias físicas y accesos digitales sin salir de la web  
- Gestionar de forma fluida sus rutas de investigación desde cualquier dispositivo  

Todo esto mediante una interfaz ágil y de consulta directa.

---

## 📌 Descripción de la Plataforma

NexusLib es una solución web diseñada para centralizar y simplificar el descubrimiento bibliográfico institucional.

El acceso está abierto para la comunidad universitaria, garantizando una consulta inmediata del stock físico local de la UPT y los recursos electrónicos externos licenciados.

---

## 🛠️ Componentes de Software

El sistema está construido utilizando tecnologías estables y de alto rendimiento:

- Backend distribuido desarrollado íntegramente con PHP 8.2.12  
- Frontend web adaptable con diseño ligero optimizado para smartphones  
- Persistencia relacional estructurada con el motor de base de datos MySQL  
- Administración de datos simplificada mediante el entorno cliente HeidiSQL  

---

## ⚙️ Flujo de Trabajo y Calidad

El proyecto utiliza herramientas automáticas para organizar y revisar el código:

- **GitHub**: Control de versiones, tareas del equipo y documentación
- **GitHub Actions**: Automatización de los procesos de revisión del sistema
- **SonarCloud**: Evaluación automática para asegurar la calidad del código
- **Snyk Code**: Escaneo continuo para detectar errores de seguridad

---

## 🏗️ Diseño Arquitectónico

El sistema está diseñado completamente bajo una arquitectura de microservicios.

Cada componente funciona de forma independiente para cumplir una tarea específica:
- Tienen su propia lógica y base de datos aislada
- Se comunican de forma eficiente entre ellos mediante consultas internas (APIs)
- Permiten actualizar o reparar un servicio sin afectar al resto de la página

---

## 🔧 Ecosistema de Microservicios

El sistema se divide en componentes especializados de negocio:

- **gateway-service**: Orquestador y consolidador de búsquedas en tiempo real
- **auth-service**: Gestión autónoma de usuarios, login y seguridad de sesiones
- **inventory-service**: Conector directo al inventario físico de la UPT
- **user-library-service**: Control de favoritos, colecciones y reservas del alumno
- **alpha-service / elibro-service**: Módulos de scraping para catálogos virtuales externos

---

## 🚀 Despliegue del Sistema

El proyecto está preparado para operar tanto en entornos de prueba como en la nube:

- Se utiliza XAMPP para el desarrollo interno y las pruebas locales
- Está desplegado en una Máquina Virtual dentro de Microsoft Azure
- Funciona con Windows Server 2025 Datacenter en la región Canada Central

---

## 🎯 Funcionalidades Principales

El sistema permite a los usuarios:

- Consultar simultáneamente el inventario físico y los catálogos virtuales  
- Registrarse e iniciar sesión de manera segura con control de perfiles  
- Agregar libros a un espacio personalizado de libros guardados  
- Organizar libros en carpetas y colecciones académicas dinámicas  
- Generar solicitudes de reserva de libros en estantería física  

---

## 🔐 Control de Accesos

El acceso al sistema se realiza mediante autenticación nativa y segura.

Solo usuarios registrados en el sistema pueden ingresar, validando sus credenciales mediante contraseñas protegidas con algoritmos de hashing.

Una vez autenticado, el sistema asigna roles específicos para gestionar los privilegios dentro de la plataforma.

---

## 👤 Panel de Usuario

El sistema incluye un espacio personal para el alumno que permite:

- Consultar la información detallada de cada libro
- Copiar los datos de los libros en formato APA
- Revisar el estado de las reservas de libros físicos
- Organizar los libros favoritos mediante colecciones usando carpetas

El acceso está adaptado completamente para una visualización móvil fluida.

---

## 🛡️ Panel de Administración

El sistema incluye un panel administrativo para el bibliotecario que permite:

- Visualizar y auditar todas las solicitudes de reserva de libros  
- Cambiar los estados de las reservas tras la entrega del ejemplar  
- Gestionar los usuarios permitiendo eliminar cuentas de la plataforma  

El acceso está estrictamente restringido según el rol asignado a la cuenta.

---

## 🚫 Limitaciones Técnicas

El sistema presenta algunas condiciones identificadas:

- Acceso dependiente de una conexión estable a Internet  
- Sujeción total a la disponibilidad y tiempos de respuesta de las APIs externas  
- Vulnerabilidad ante puntos únicos de fallo al alojar los servicios en una sola VM  

---

## 🎯 Conclusión General

Se obtiene una plataforma:

- Funcional y centralizada que resuelve la fragmentación actual  
- Económicamente sustentada con indicadores financieros viables  
- Modular y escalable mediante servicios distribuidos  
- Desplegada en la nube lista para su uso operativo  
