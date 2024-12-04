create database Outsourcing1;

CREATE TABLE Rol (
  codigo VARCHAR(3) PRIMARY KEY,
  nombre VARCHAR(25) NOT NULL
);

CREATE TABLE Usuario (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  correo VARCHAR(25) NOT NULL,
  contrasenia VARCHAR(25) NOT NULL,
  rol VARCHAR(3) NOT NULL,
  FOREIGN KEY (rol) REFERENCES Rol(codigo)
);

CREATE TABLE Prospecto (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(30) NOT NULL,
  primerApellido VARCHAR(30) NOT NULL,
  segundoApellido VARCHAR(30),
  resumen VARCHAR(300),
  fechaNacimiento DATE NOT NULL,
  usuario INT NOT NULL,
  FOREIGN KEY (usuario) REFERENCES Usuario(numero)
);

ALTER TABLE Prospecto
ADD COLUMN resumen VARCHAR(300);

CREATE TABLE Empresa (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(30) NOT NULL,
  ciudad VARCHAR(30) NOT NULL,
  calle VARCHAR(30) NOT NULL,
  numeroCalle INT NOT NULL,
  colonia VARCHAR(30) NOT NULL,
  codigoPostal INT NOT NULL,
  nombreCont VARCHAR(30) NOT NULL,
  primerApellidoCont VARCHAR(30) NOT NULL,
  segundoApellidoCont VARCHAR(30),
  usuario INT NOT NULL,
  FOREIGN KEY (usuario) REFERENCES Usuario(numero)
);

CREATE TABLE Tipo_Contrato (
  codigo VARCHAR(5) PRIMARY KEY,
  nombre VARCHAR(30) NOT NULL,
  descripcion VARCHAR(150) NOT NULL
);

CREATE TABLE Vacante (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  titulo VARCHAR(30) NOT NULL,
  descripcion VARCHAR(250) NOT NULL,
  salario INT,
  es_directo BOOLEAN NOT NULL,
  cantEmpleados INT NOT NULL,
  fechaInicio DATE NOT NULL,
  fechaCierre DATE NOT NULL,
  diasRestantes INT NOT NULL,
  estado BOOLEAN NOT NULL,
  tipo_contrato VARCHAR(5) NOT NULL,
  empresa INT NOT NULL,
  FOREIGN KEY (tipo_contrato) REFERENCES Tipo_Contrato(codigo),
  FOREIGN KEY (empresa) REFERENCES Empresa(numero)
);

CREATE TABLE Requerimiento (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  descripcion VARCHAR(50) NOT NULL,
  vacante INT NOT NULL,
  FOREIGN KEY (vacante) REFERENCES Vacante(numero)
);

CREATE TABLE Contrato (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  fechaInicio DATE NOT NULL,
  fechaCierre DATE NOT NULL,
  prospecto INT NOT NULL,
  vacante INT NOT NULL,
  tipo_contrato VARCHAR(5) NOT NULL,
  FOREIGN KEY (prospecto) REFERENCES Prospecto(numero),
  FOREIGN KEY (vacante) REFERENCES Vacante(numero),
  FOREIGN KEY (tipo_contrato) REFERENCES Tipo_Contrato(codigo)
);

CREATE TABLE Carrera (
  codigo VARCHAR(5) PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL
);

alter table carrera
modify column nombre varchar(100)

CREATE TABLE Curso (
  codigo VARCHAR(5) PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  descripcion VARCHAR(300) NOT NULL,
  duracion INT NOT NULL
);

CREATE TABLE Leccion (
  numero VARCHAR(5) PRIMARY KEY,
  titulo VARCHAR(30) NOT NULL,
  descripcion VARCHAR(200) NOT NULL,
  contenido VARCHAR(5000) NOT NULL,
  curso VARCHAR(5) NOT NULL,
  FOREIGN KEY (curso) REFERENCES Curso(codigo)
);

CREATE TABLE Certificacion (
  codigo VARCHAR(5) PRIMARY KEY,
  nombre VARCHAR(30) NOT NULL,
  descripcion VARCHAR(200),
  curso VARCHAR(5) NOT NULL,
  FOREIGN KEY (curso) REFERENCES Curso(codigo)
);

CREATE TABLE Evaluacion (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(30) NOT NULL,
  descripcion VARCHAR(200) NOT NULL,
  curso VARCHAR(5) NOT NULL,
  FOREIGN KEY (curso) REFERENCES Curso(codigo)
);

CREATE TABLE Pregunta (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  texto VARCHAR(200) NOT NULL,
  evaluacion INT NOT NULL,
  FOREIGN KEY (evaluacion) REFERENCES Evaluacion(numero)
);

CREATE TABLE Respuesta (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  texto VARCHAR(200) NOT NULL,
  es_correcta BOOLEAN NOT NULL,
  pregunta INT NOT NULL,
  FOREIGN KEY (pregunta) REFERENCES Pregunta(numero)
);

CREATE TABLE Plan_suscripcion (
  codigo VARCHAR(5) PRIMARY KEY,
  duracion INT NOT NULL,
  precio DECIMAL(5, 2) NOT NULL,
  precioMensual DECIMAL(5, 2) NOT NULL
);

CREATE TABLE Membresia (
  numero INT PRIMARY KEY AUTO_INCREMENT,
  fechaVencimiento DATE NOT NULL,
  estatus BOOLEAN NOT NULL,
  empresa INT NOT NULL,
  plan_suscripcion VARCHAR(5) NOT NULL,
  FOREIGN KEY (empresa) REFERENCES Empresa(numero),
  FOREIGN KEY (plan_suscripcion) REFERENCES Plan_suscripcion(codigo)
);

CREATE TABLE Examenes_aplicados (
  prospecto INT NOT NULL,
  evaluacion INT NOT NULL,
  calificacion INT NOT NULL,
  PRIMARY KEY (prospecto, evaluacion),
  FOREIGN KEY (prospecto) REFERENCES Prospecto(numero),
  FOREIGN KEY (evaluacion) REFERENCES Evaluacion(numero)
);

CREATE TABLE Cursos_inscritos (
  prospecto INT NOT NULL,
  curso VARCHAR(5) NOT NULL,
  PRIMARY KEY (prospecto, curso),
  FOREIGN KEY (prospecto) REFERENCES Prospecto(numero),
  FOREIGN KEY (curso) REFERENCES Curso(codigo)
);

CREATE TABLE Carreras_estudiadas (
  prospecto INT NOT NULL,
  carrera VARCHAR(30) NOT NULL,
  anioConcluido INT,
  PRIMARY KEY (prospecto, carrera),
  FOREIGN KEY (prospecto) REFERENCES Prospecto(numero),
  FOREIGN KEY (carrera) REFERENCES Carrera(codigo)
);

CREATE TABLE Carreras_solicitadas (
  vacante INT NOT NULL,
  carrera VARCHAR(30) NOT NULL,
  PRIMARY KEY (vacante, carrera),
  FOREIGN KEY (vacante) REFERENCES Vacante(numero),
  FOREIGN KEY (carrera) REFERENCES Carrera(codigo)
);

CREATE TABLE Certificaciones_obtenidas (
  prospecto INT NOT NULL,
  certificacion VARCHAR(5) NOT NULL,
  fechaEmision DATE NOT NULL,
  PRIMARY KEY (prospecto, certificacion),
  FOREIGN KEY (prospecto) REFERENCES Prospecto(numero),
  FOREIGN KEY (certificacion) REFERENCES Certificacion(codigo)
);

CREATE TABLE Certificaciones_requeridas (
  vacante INT NOT NULL,
  certificacion VARCHAR(5) NOT NULL,
  PRIMARY KEY (vacante, certificacion),
  FOREIGN KEY (vacante) REFERENCES Vacante(numero),
  FOREIGN KEY (certificacion) REFERENCES Certificacion(codigo)
);

INSERT INTO Rol (codigo, nombre) VALUES
('ADM', 'Administrador'),
('EMP', 'Empresa'),
('PRO', 'Prospecto');

INSERT INTO Usuario (correo, contrasenia, rol) VALUES
('admin@gmail.com', '12345', 'ADM'),
('empresa@gmail.com', '12345', 'EMP'),
('prospecto@gmail.com', '12345', 'PRO'),
('empresa2@gmail.com', '12345', 'EMP'),
('prospecto2@gmail.com', '12345', 'PRO');

select * from usuario

INSERT INTO Prospecto (nombre, primerApellido, segundoApellido, fechaNacimiento, usuario) VALUES
('Juan', 'Pérez', 'González', '1990-01-01', 3),
('María', 'García', 'Rodríguez', '1992-02-02', 5);

INSERT INTO Empresa (nombre, ciudad, calle, numeroCalle, colonia, codigoPostal, nombreCont, primerApellidoCont, segundoApellidoCont, usuario) VALUES
('BitLab', 'Tijuana', 'Avenida Independencia', 123, 'Centro', 22000, 'Josue Isaac', 'Lara', 'Lopez', 2),
('Microsoft', 'Tijuana', 'Calle 5 de Mayo', 456, 'Zona Río', 22400, 'Ana', 'Martínez', 'López', 4);

INSERT INTO Tipo_Contrato (codigo, nombre, descripcion) VALUES
('TC01', 'Contrato indefinido', 'Contrato sin plazo fijo, puede ser renovado o terminado en cualquier momento'),
('TC02', 'Contrato definido (plazo fijo)', 'Contrato con un plazo fijo de duración, puede ser renovado o terminado al final del plazo'),
('TC03', 'Contrato por obra o servicio determinado', 'Contrato para realizar una obra o servicio específico, se termina cuando se completa la obra o servicio'),
('TC04', 'Contrato por proyecto', 'Contrato para realizar un proyecto específico, se termina cuando se completa el proyecto');

INSERT INTO Vacante (titulo, descripcion, salario, es_directo, cantEmpleados, fechaInicio, fechaCierre, diasRestantes, estado, tipo_contrato, empresa) VALUES
('Desarrollador de software', 'Se busca un desarrollador de software con experiencia en Java y Spring Boot para trabajar en un proyecto de desarrollo de software', 50000, TRUE, 1, '2024-01-01', '2024-01-31', 30, TRUE, 'TC04', 1),
('Ingeniero de redes', 'Se busca un ingeniero de redes con experiencia en configuración de routers y switches para trabajar en un proyecto de implementación de redes', 60000, FALSE, 2, '2024-02-01', '2024-02-28', 28, TRUE, 'TC03', 2),
('Analista de datos', 'Se busca un analista de datos con experiencia en SQL y Tableau para trabajar en un proyecto de análisis de datos', 40000, TRUE, 1, '2024-03-01', '2024-03-31', 31, TRUE, 'TC02', 1);

INSERT INTO Requerimiento (descripcion, vacante) VALUES
('Experiencia en desarrollo de software con Java y Spring Boot', 1),
('Conocimientos de bases de datos relacionales y no relacionales', 1),
('Experiencia en trabajo en equipo y colaboración con otros desarrolladores', 1),
('Experiencia en configuración de routers y switches', 2),
('Conocimientos de protocolos de red y seguridad', 2),
('Experiencia en trabajo en equipo y colaboración con otros ingenieros', 2),
('Experiencia en análisis de datos con SQL y Tableau', 3),
('Conocimientos de estadística y matemáticas', 3),
('Experiencia en trabajo en equipo y colaboración con otros analistas', 3);

INSERT INTO Contrato (fechaInicio, fechaCierre, prospecto, vacante, tipo_contrato) VALUES
('2024-04-01', '2024-06-30', 1, 1, 'TC04');

INSERT INTO Carrera (codigo, nombre) VALUES
('INGC', 'Ingeniería en Computación'),
('INGE', 'Ingeniería en Electrónica'),
('INGM', 'Ingeniería en Mecánica'),
('INGQ', 'Ingeniería en Química'),
('LICF', 'Licenciatura en Física'),
('LICB', 'Licenciatura en Biología'),
('LICP', 'Licenciatura en Psicología'),
('LICD', 'Licenciatura en Derecho'),
('INGA', 'Ingeniería en Administración'),
('INGF', 'Ingeniería en Finanzas'),
('INGR', 'Ingeniería en Recursos Humanos'),
('INGT', 'Ingeniería en Turismo'),
('LICL', 'Licenciatura en Lengua y Literatura'),
('LICN', 'Licenciatura en Nutrición'),
('LICM', 'Licenciatura en Medicina'),
('INGN', 'Ingeniería en Naval'),
('INGG', 'Ingeniería en Geología'),
('INGP', 'Ingeniería en Petróleo');

INSERT INTO Carrera (codigo, nombre) VALUES
('INDS', 'Ingeniería en Desarrollo de Software'),

INSERT INTO Curso (codigo, nombre, descripcion, duracion) VALUES
('CE01', 'Control de Emociones en el Trabajo', 'Este curso te enseñará a manejar tus emociones en el trabajo y a mejorar tus relaciones con tus colegas y clientes.', 1);

INSERT INTO Leccion (numero, titulo, descripcion, contenido, curso) VALUES
('LEC01', 'Introducción al Control de Emociones', 'En esta lección, aprenderás a entender la importancia del control de emociones en el trabajo y cómo puede afectar tu productividad y bienestar.', 'El control de emociones es fundamental para tener éxito en el trabajo. Cuando estamos emocionalmente estables, podemos tomar decisiones más informadas y trabajar de manera más efectiva. En esta lección, exploraremos la importancia del control de emociones y cómo puede afectar tu vida laboral.', 'CE01'),
('LEC02', 'Reconociendo tus Emociones', 'En esta lección, aprenderás a identificar y reconocer tus emociones y cómo pueden afectar tu comportamiento en el trabajo.', 'Es importante reconocer tus emociones para poder manejarlas de manera efectiva. En esta lección, exploraremos cómo identificar tus emociones y cómo pueden afectar tu comportamiento en el trabajo.', 'CE01'),
('LEC03', 'Gestión del Estrés', 'En esta lección, aprenderás técnicas para manejar el estrés y la ansiedad en el trabajo.', 'El estrés y la ansiedad pueden ser un gran obstáculo para el éxito en el trabajo. En esta lección, exploraremos técnicas para manejar el estrés y la ansiedad y cómo pueden ayudarte a mejorar tu productividad.', 'CE01'),
('LEC04', 'Comunicación Efectiva', 'En esta lección, aprenderás a comunicarte de manera efectiva con tus colegas y clientes.', 'La comunicación efectiva es fundamental para el éxito en el trabajo. En esta lección, exploraremos cómo comunicarte de manera efectiva y cómo puede ayudarte a mejorar tus relaciones con tus colegas y clientes.', 'CE01'),
('LEC05', 'Resolución de Conflictos', 'En esta lección, aprenderás a resolver conflictos de manera efectiva en el trabajo.', 'Los conflictos pueden ser un gran obstáculo para el éxito en el trabajo. En esta lección, exploraremos cómo resolver conflictos de manera efectiva y cómo puede ayudarte a mejorar tus relaciones con tus colegas y clientes.', 'CE01'),
('LEC06', 'Autoconocimiento y Autoestima', 'En esta lección, aprenderás a desarrollar un mayor autoconocimiento y autoestima.', 'El autoconocimiento y la autoestima son fundamentales para el éxito en el trabajo. En esta lección, exploraremos cómo desarrollar un mayor autoconocimiento y autoestima y cómo puede ayudarte a mejorar tu productividad.', 'CE01'),
('LEC07', 'Practicando el Control de Emociones', 'En esta lección, aprenderás a practicar el control de emociones en tu vida laboral.', 'En esta lección, exploraremos cómo practicar el control de emociones en tu vida laboral y cómo puede ayudarte a mejorar tu productividad y bienestar.', 'CE01');

INSERT INTO Certificacion (codigo, nombre, descripcion, curso) VALUES
('CEC01', 'Certificado en Control de Emociones en el Trabajo', 'Este certificado acredita que el titular ha completado el curso de Control de Emociones en el Trabajo y ha demostrado una comprensión profunda de los conceptos y técnicas para manejar las emociones en el trabajo.', 'CE01');

INSERT INTO Evaluacion (nombre, descripcion, curso) VALUES
('Evaluación de Control de Emociones en el Trabajo', 'Esta evaluación evalúa la comprensión de los conceptos y técnicas para manejar las emociones en el trabajo.', 'CE01');

INSERT INTO Pregunta (texto, evaluacion) VALUES
('¿Cuál es el objetivo principal del control de emociones en el trabajo?', 1),
('¿Qué es el estrés y cómo puede afectar el desempeño laboral?', 1),
('¿Cuál es la técnica más efectiva para manejar la ansiedad en el trabajo?', 1),
('¿Cómo se puede mejorar la comunicación efectiva en el trabajo?', 1),
('¿Qué es la autoestima y cómo se puede desarrollar en el trabajo?', 1);

INSERT INTO Respuesta (texto, es_correcta, pregunta) VALUES
('Mejorar la productividad y el desempeño laboral', TRUE, 1),
('Reducir el estrés y la ansiedad', FALSE, 1),
('Mejorar la comunicación con los colegas', FALSE, 1),
('El estrés es una respuesta natural del cuerpo a una situación estresante y puede afectar el desempeño laboral si no se maneja adecuadamente', TRUE, 2),
('La ansiedad es una emoción que puede afectar el desempeño laboral si no se maneja adecuadamente', FALSE, 2),
('La técnica de respiración profunda', TRUE, 3),
('La técnica de relajación muscular', FALSE, 3),
('La comunicación efectiva implica escuchar activamente y responder de manera clara y concisa', TRUE, 4),
('La comunicación efectiva implica hablar de manera clara y concisa', FALSE, 4),
('La autoestima es la confianza en uno mismo y se puede desarrollar mediante la práctica de la auto-reflexión y la auto-aceptación', TRUE, 5),
('La autoestima es la confianza en los demás y se puede desarrollar mediante la práctica de la empatía y la comprensión', FALSE, 5);

INSERT INTO Plan_suscripcion (codigo, duracion, precio, precioMensual) VALUES
('BAS01', 1, 999.99, 999.99),
('PRE01', 6, 5499.99, 916.66),
('PRO01', 12, 9999.99, 833.33);


INSERT INTO Membresia (fechaVencimiento, estatus, empresa, plan_suscripcion) VALUES
('2024-12-31', TRUE, 1, 'PRO01'),
('2025-06-30', TRUE, 2, 'PRE01');

INSERT INTO Cursos_inscritos (prospecto, curso) VALUES
(1, 'CE01'),
(2, 'CE01');

INSERT INTO Examenes_aplicados (prospecto, evaluacion, calificacion) VALUES
(1, 1, 80),
(2, 1, 90);

INSERT INTO Carreras_estudiadas (prospecto, carrera, anioConcluido) VALUES
(1, 'INGC', 2020),
(2, 'LICM', 2019);

INSERT INTO Carreras_solicitadas (vacante, carrera) VALUES
(1, 'INGC'),
(1, 'LICM'),
(2, 'INGE'),
(3, 'LICF'),
(3, 'INGC');

INSERT INTO Certificaciones_obtenidas (prospecto, certificacion, fechaEmision) VALUES
(1, 'CEC01', '2024-01-01'),
(2, 'CEC01', '2024-02-15');

INSERT INTO Certificaciones_requeridas (vacante, certificacion) VALUES
(1, 'CEC01');