import requests
import json
from bs4 import BeautifulSoup


def get_page_speed_scores(url):
    api_url = f"https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url={url}&strategy=mobile"
    response = requests.get(api_url)
    if response.status_code == 200:
        data = response.json()
        categories = data["lighthouseResult"]["categories"]
        print(data["lighthouseResult"]["audits"])
        print(json.dumps(categories, indent=4))
        return {
            "performance": int(categories["performance"]["score"] * 100),
            "accessibility": int(categories["accessibility"]["score"] * 100),
            "best_practices": int(categories["best-practices"]["score"] * 100),
            "seo": int(categories["seo"]["score"] * 100),
        }
    return None


def check_ssl(url):
    try:
        response = requests.get(url, timeout=10)
        return response.url.startswith("https")
    except:
        return False


def get_meta_tags(url):
    try:
        response = requests.get(url)
        soup = BeautifulSoup(response.text, "html.parser")
        return {
            "title": soup.title.string if soup.title else "No title found",
            "description": soup.find("meta", attrs={"name": "description"})["content"]
            if soup.find("meta", attrs={"name": "description"})
            else "No description found",
        }
    except:
        return {"title": "Error", "description": "Error fetching meta tags"}


def score_to_color(score):
    if score >= 90:
        return "green"
    elif score >= 50:
        return "orange"
    else:
        return "red"


def generate_score_html(category, score):
    return f"""
    <div class="score-category">
        <h3>{category.capitalize()}</h3>
        <div class="score-circle" style="background: conic-gradient(
            {score_to_color(score)} {score * 3.6}deg,
            #ccc {score * 3.6}deg 360deg
        );">
            <span>{score}</span>
        </div>
        <p>{score_to_color(score).capitalize()}</p>
    </div>
    """


def generate_report_html(url):
    scores = get_page_speed_scores(url)
    ssl_status = check_ssl(url)
    meta_tags = get_meta_tags(url)

    if not scores:
        return "<p>Error fetching PageSpeed Insights data</p>"

    scores_html = "".join(
        [generate_score_html(cat, score) for cat, score in scores.items()]
    )

    html = f"""
    <section id="website-report">
        <h1>Website Performance Report</h1>

        <h2>PageSpeed Insights Scores</h2>
        <div class="speed-scores">
            {scores_html}
        </div>

        <h2>Security</h2>
        <p>SSL Certificate: {'Installed' if ssl_status else 'Not Installed'}</p>

        <h2>SEO Basics</h2>
        <p>Title Tag: {meta_tags['title']}</p>
        <p>Meta Description: {meta_tags['description']}</p>

        <h2>Expert Analysis</h2>
        <p>Based on these metrics, here are our recommendations:</p>
        <ul>
            {'<li>Improve page load time and performance to boost user experience and SEO ranking. Start with compress images and use a cropped version for mobile.</li>' if scores['performance'] < 80 else ''}
            {'<li>Enhance accessibility to ensure your website is usable by all visitors. Start by adding "Skip to main content".</li>' if scores['accessibility'] < 90 else ''}
            {'<li>Address best practices issues to improve overall website quality</li>' if scores['best_practices'] < 90 else ''}
            {'<li>Optimize on-page SEO elements to improve search engine visibility</li>' if scores['seo'] < 90 else ''}
            {'<li>Install an SSL certificate to secure your website and improve trust</li>' if not ssl_status else ''}
            {'<li>Refine your title tag and meta description for better click-through rates</li>' if len(meta_tags['description']) < 50 or len(meta_tags['title']) < 10 else ''}
        </ul>
    </section>
    """
    return html


# Example usage
url = "https://northmelbourneweb.com"
report_html = generate_report_html(url)
print(report_html)
