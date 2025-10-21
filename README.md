#  Plataforma de Crowdfunding con Transparencia

##  Descripción General
Proyecto académico desarrollado como parte del curso **Desarrollo de Software 2**.  
El objetivo es construir una **plataforma web de crowdfunding** que garantice **transparencia total en la gestión de fondos**, permitiendo que los creadores administren proyectos y los colaboradores auditen en tiempo real la ejecución financiera.

El sistema se implementa bajo el **paradigma de Programación Orientada a Objetos (POO)**, aplicando el marco ágil **Scrum**, y utilizando el **framework Laravel**, que sigue la arquitectura **MVC (Modelo–Vista–Controlador)**.  
Además, se aplican prácticas de **TDD (Test-Driven Development)**, **refactorización continua**, y principios de **modularidad, cohesión alta y bajo acoplamiento**.

---

## 👥 Equipo de Desarrollo
| Nombre | 
|---------|
| **Nicolás Rodríguez** |
| **Marco Herrera** |
| **Cristian Maldonado** |
| **Kevin Restrepo** |
| **Kevin Libreros** |


## 🧩 Entrega 1 – Diseño e Infraestructura

### 🎯 Objetivos
- Análisis, levantamiento de requerimientos y diseño del sistema.  
- Configuración de la infraestructura de desarrollo y repositorio.  
- Creación del backlog y tablero de control en GitHub Projects.  

### 🧱 Componentes entregados
1. **Diagramas UML y de análisis**
   - Diagrama de **Casos de uso**.  
   - Diagrama de **Flujo de Datos (DFD)** niveles 0 y 1.  
   - Diagrama **Entidad–Relación (ER)** y modelo relacional.  
   - Diagrama de **Clases**.  

2. **Configuración técnica**
   - Repositorio GitHub: `crowfunding_2`  
   - Framework: **Laravel (PHP 8.x)**  
   - Arquitectura: **MVC**  
   - Base de datos: **MariaDB**  
   - Estrategia de ramas: `main`, `develop`, `feature/*`  
   - Tablero Kanban: **CrowFunding desarrollo 2**  
   - Gestión ágil con **Scrum**

---

## Paradigma, Framework y Arquitectura

### Paradigma de Programación
El proyecto está desarrollado bajo el paradigma de **Programación Orientada a Objetos (POO)**, permitiendo crear un sistema modular y escalable mediante:
- Clases y objetos reutilizables.  
- Encapsulamiento de datos.  
- Herencia y polimorfismo.  
- Bajo acoplamiento entre componentes.

### Framework y Arquitectura
- **Framework:** Laravel 11  
- **Arquitectura:** MVC (Modelo–Vista–Controlador)

**Estructura del sistema:**
- **Modelo (M):** gestiona la lógica de negocio y conexión con la base de datos.  
- **Vista (V):** interfaz de usuario, desarrollada con Blade Templates.  
- **Controlador (C):** coordina la comunicación entre modelo y vista.  

Laravel provee un entorno seguro, modular y escalable con manejo de rutas, controladores, migraciones y Eloquent ORM.


##  Base de Datos

- **Gestor:** MariaDB  
- **Modelo:** relacional  
- **Características principales:**
  - Tablas normalizadas (3FN).  
  - Relaciones entre usuarios, proyectos, recompensas y transacciones.  
  - Integridad referencial mediante claves foráneas.  
  - Seguridad en transacciones de fondos.



📎 **Referencias:**
- [ Diagrama de flujo de datos (Google Drive)](https://drive.google.com/file/d/1E74EZBCtWtK_KlUEYS71jvZyvDmcCK-Y/view?usp=drive_link)
- [ Modelo Entidad-Relación y físico (diagrams.net)](https://app.diagrams.net/?libs=general;er#G1U6v0B8HN7QTI8B-7y3L-S5haXQ99Tv2m#%7B%22pageId%22%3A%22XPA24Rqfg-Av8ghFqx-V%22%7D)

---

## Diagramas de Diseño

Los diagramas elaborados para esta fase incluyen:

| Tipo | Enlace |
|------|--------------------|
| Diagrama de flujo de datos (DFD) niveles 0 y 1 | [Ver en Google Drive](https://drive.google.com/file/d/1ZOymZqTG-Ta6wRwX3JkgrnLvZstWIeWG/view?usp=drive_link) |
| Modelo Entidad-Relación (ER) | [Ver en diagrams.net](https://app.diagrams.net/?libs=general;er#G1U6v0B8HN7QTI8B-7y3L-S5haXQ99Tv2m#%7B%22pageId%22%3A%22XPA24Rqfg-Av8ghFqx-V%22%7D) |


