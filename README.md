# 🎬 Movie Rating App

A small **Laravel + Livewire** project for rating movies.

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-4E56A6?style=for-the-badge&logo=livewire&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)

## 📋 Table of Contents

- [Installation](#-installation)
- [Configuration](#️-configuration)
- [Usage](#-usage)
- [Tests](#-tests)
- [Notes](#-notes)
- [Tech Stack](#️-tech-stack)

## 🚀 Installation

### Requirements

- **Docker** and **Docker Compose**
- **TMDB API Key** (The Movie Database)

### Setup

1. **Run the setup script:**
   ```bash
   ./setup.sh
   ```

2. **Configure environment variables:**
   Open the `.env` file and add your TMDB API key:
   ```env
   TMDB_API_KEY=your_api_key_here
   ```

## ⚙️ Configuration

Make sure you have a valid TMDB API key.  
You can create one at: https://www.themoviedb.org/settings/api

## 📱 Usage

### Registration / Login

1. Register a new user or log in with an existing account  
2. After successful login, you'll be redirected to the **dashboard**

### Dashboard

The dashboard is the core of the application and provides the following features:

#### Movie Search

- **Empty search field:** Displays all movies rated by the current user  
- **With search term:**  
  - First checks for matches in the local database  
  - If no match is found, movies are automatically fetched from the TMDB API  
  - Search results are updated in real time  

#### Movie Rating

- Click on the stars below a movie to submit your personal rating  
- Your rating is shown as filled stars  
- The average rating of all users is displayed alongside your rating  
- Ratings can be changed at any time  

## 🧪 Tests

To run the test suite, use the following commands:

```bash
# Enter the Docker container
docker compose exec app bash

# Run movie tests
php artisan test tests/Feature/MovieTest.php
```

## 📝 Notes

- **TMDB API Key required:** Without a valid API key, new movies cannot be imported from the TMDB database  
- **Docker development environment:** The project is preconfigured for local development with Docker  
- **Livewire:** The UI is powered by Laravel Livewire for reactive components  
- **Automatic import:** Movies are automatically fetched and stored locally on first search  

## 🛠️ Tech Stack

- **Backend:** Laravel  
- **Frontend:** Livewire  
- **Containerization:** Docker  
- **API:** The Movie Database (TMDB)  
- **Testing:** PHPUnit

---

<div align="center">
  <p>Built with Laravel & Livewire</p>
</div>
