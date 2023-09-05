import React, {useEffect, useState} from "react";
import RecipeSearchList from "../../common/components/RecipeSearchList";

const Recipes = ({recipes, base}) => {
    const [showRecipes, setShowRecipes] = useState(recipes);
    const [loading, setLoading] = useState(!recipes);

    useEffect(() => {
        if (!loading) {
            return;
        }

        fetch(base + "/api/recipes")
            .then(response => {

                return response.json();
            })
            .then(data => {
                setShowRecipes(data);
                setLoading(false);
            });
    }, []);

    if (loading) {
        return <div>Loading...</div>;
    }

    return (
        <>
            <ol className="breadcrumb">
                <li className="active">Recipes</li>
            </ol>

            <RecipeSearchList
                recipes={recipes}
                routePrefix={base}
            />
        </>
    );
}

export default Recipes;
